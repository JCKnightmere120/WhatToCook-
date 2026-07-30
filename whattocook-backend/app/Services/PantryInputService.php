<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PantryInputService
{
    public function __construct(private IngredientCatalogService $catalog) {}
    /** Build safe, editable pantry candidates. Nothing is persisted here. */
    public function fromBarcode(string $barcode): array
    {
        $product = null;
        try {
            $product = Http::acceptJson()->timeout(6)
                ->get(config('services.open_food_facts.base_url').'/api/v2/product/'.$barcode.'.json')
                ->throw()->json('product');
        } catch (\Throwable) {
            // A code may be private, local, or absent from the public catalogue. Manual review remains possible.
        }

        $name = trim((string) ($product['product_name_en'] ?? $product['product_name'] ?? $product['brands'] ?? ''));
        return [
            'source' => 'barcode',
            'barcode' => $barcode,
            'needs_review' => true,
            'message' => $name ? 'Product found. Please confirm its pantry details.' : 'Product was not found. Enter its name and confirm the details.',
            ...$this->groupCandidates([$this->candidate($name, $product['quantity'] ?? null)]),
        ];
    }

    public function fromVoice(string $transcript): array
    {
        return $this->fromText($transcript, 'voice');
    }

    public function fromText(string $text, string $source): array
    {
        return [
            'source' => $source,
            'needs_review' => true,
            'message' => 'Voice input was converted into editable pantry details. Please verify it before saving.',
            ...$this->groupCandidates($this->parseText($text)),
        ];
    }

    public function fromReceipt(UploadedFile $receipt, ?string $recognizedText): array
    {
        // Keep the original only long enough for an optional OCR provider/queued job to process it.
        $path = $receipt->store('receipt-inputs', 'local');
        $text = trim((string) $recognizedText);

        return [
            'source' => 'receipt',
            'receipt_reference' => basename($path),
            'needs_review' => true,
            'message' => $text !== ''
                ? 'Receipt text was converted into editable candidates. Verify each item before saving.'
                : 'Receipt uploaded. OCR is not configured yet, so enter or paste the receipt text to create candidates.',
            ...$this->groupCandidates($text !== '' ? $this->parseText($text) : []),
        ];
    }

    private function parseText(string $text): array
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/\b(i have|add|please add|we bought)\b/', '', $text) ?? $text;
        $numberWords = ['one' => '1', 'two' => '2', 'three' => '3', 'four' => '4', 'five' => '5', 'six' => '6', 'seven' => '7', 'eight' => '8', 'nine' => '9', 'ten' => '10'];
        $text = preg_replace_callback('/\b('.implode('|', array_keys($numberWords)).')\b/', fn ($match) => $numberWords[$match[1]], $text) ?? $text;
        $lines = preg_split('/[\r\n,;]+|\s+and\s+/', $text) ?: [];
        $candidates = [];
        foreach ($lines as $line) {
            $line = trim(preg_replace('/\s+/', ' ', $line) ?? '');
            if ($line === '') continue;
            preg_match('/^(?:(\d+(?:\.\d+)?)\s*(kg|g|grams?|ml|l|lit(?:er|re)?s?|pcs?|pieces?|cans?|packs?|bottles?)?\s*(?:of\s+)?)?(.+)$/i', $line, $matches);
            $name = trim($matches[3] ?? $line);
            // Ignore common receipt headings and totals without making assumptions about items.
            if (preg_match('/^(total|subtotal|change|cash|vat|date|receipt)\b/i', $name)) continue;
            $candidates[] = $this->candidate($name, isset($matches[1]) ? trim($matches[1].' '.($matches[2] ?? '')) : null);
        }
        return array_slice($candidates, 0, 30);
    }

    private function candidate(string $name, ?string $quantityText): array
    {
        preg_match('/^(\d+(?:\.\d+)?)\s*(.*)$/', trim((string) $quantityText), $amount);
        $candidate = [
            'name' => Str::title(trim($name)),
            'quantity' => $amount[1] ?? null,
            'unit' => trim($amount[2] ?? '') ?: null,
            'purchase_source' => 'unknown',
            'storage_type' => 'unknown',
        ];
        return [...$candidate, ...$this->catalog->resolve($name)];
    }

    private function groupCandidates(array $candidates): array
    {
        return ['accepted' => array_values(array_filter($candidates, fn ($item) => $item['status'] === 'accepted')), 'suggested' => array_values(array_filter($candidates, fn ($item) => $item['status'] === 'suggested')), 'rejected' => array_values(array_filter($candidates, fn ($item) => $item['status'] === 'rejected')), 'candidates' => array_values(array_filter($candidates, fn ($item) => $item['status'] !== 'rejected'))];
    }
}
