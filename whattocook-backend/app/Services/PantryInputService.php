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
            ...$this->groupCandidates($name !== '' ? [$this->candidate($name, $product['quantity'] ?? null)] : []),
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
            'message' => $source === 'receipt'
                ? 'Receipt text was converted into editable pantry candidates. Verify each item before saving.'
                : 'Voice input was converted into editable pantry details. Please verify it before saving.',
            ...$this->groupCandidates($this->parseText($text)),
        ];
    }

    public function fromReceipt(UploadedFile $receipt, ?string $recognizedText): array
    {
        $text = trim((string) $recognizedText);

        return [
            'source' => 'receipt',
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
        // Keep receipt lines intact, but understand common Filipino quantity
        // words and units before candidate review.
        $text = strtr($text, ['isang' => '1', 'dalawang' => '2', 'dalawa' => '2', 'tatlong' => '3', 'tatlo' => '3']);
        $rawLines = preg_split('/[\r\n,;]+|\s+(?:and|at)\s+/', $text) ?: [];
        // Speech recognition often omits punctuation and the word "and".
        // A new numeric quantity is therefore also a reliable boundary:
        // "2 eggs 2 onions 2 boxes of milk" becomes three candidates.
        $lines = [];
        $unitPattern = 'kg|g|grams?|ml|l|lit(?:er|re)?s?|pcs?|pieces?|piraso|lata|cans?|packs?|pakete|bottles?|botelya|boxes?|kahons?';
        foreach ($rawLines as $rawLine) {
            $rawLine = trim($rawLine);
            if (preg_match_all('/(?:^|\s)(\d+(?:\.\d+)?)\s*(?:('.$unitPattern.')\s*)?(?:of\s+)?(.+?)(?=\s+\d+(?:\.\d+)?\s*(?:'.$unitPattern.')?\s*(?:of\s+)?|$)/i', $rawLine, $found, PREG_SET_ORDER)) {
                foreach ($found as $part) {
                    $lines[] = trim($part[1].' '.($part[2] ?? '').' '.($part[3] ?? ''));
                }
            } else {
                $lines[] = $rawLine;
            }
        }
        $candidates = [];
        foreach ($lines as $line) {
            $line = trim(preg_replace('/\s+/', ' ', $line) ?? '');
            if ($line === '') continue;
            // Receipts often end with a price. It is not an inventory amount.
            $line = preg_replace('/(?:\s+|\s*@\s*)₱?\d+(?:\.\d{2})\s*$/u', '', $line) ?? $line;
            if (preg_match('/^(?:₱?\d+(?:\.\d{2})?|\d+\s*x\s*₱?\d+(?:\.\d{2})?)$/iu', $line)) continue;
            preg_match('/^(?:(\d+(?:\.\d+)?)\s*('.$unitPattern.')?\s*(?:of\s+)?)?(.+)$/i', $line, $matches);
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
            'unit' => $this->normaliseUnit(trim($amount[2] ?? '')),
            'purchase_source' => 'unknown',
            'storage_type' => 'unknown',
        ];
        return [...$candidate, ...$this->catalog->resolve($name)];
    }

    private function groupCandidates(array $candidates): array
    {
        // One spoken/printed item may appear twice (e.g. a receipt's English
        // and Filipino description). Combine only same-review candidates with
        // the same unit; incompatible units remain separate for the user.
        $deduped = [];
        foreach ($candidates as $candidate) {
            $canonical = $candidate['ingredient']['canonical_name'] ?? $candidate['suggestion']['canonical_name'] ?? strtolower($candidate['name']);
            $key = $candidate['status'].'|'.strtolower($canonical).'|'.strtolower((string) $candidate['unit']);
            if (isset($deduped[$key]) && is_numeric($candidate['quantity']) && is_numeric($deduped[$key]['quantity'])) {
                $deduped[$key]['quantity'] = (string) round((float) $deduped[$key]['quantity'] + (float) $candidate['quantity'], 3);
            } else {
                $deduped[$key] = $candidate;
            }
        }
        $candidates = array_values($deduped);
        return ['accepted' => array_values(array_filter($candidates, fn ($item) => $item['status'] === 'accepted')), 'suggested' => array_values(array_filter($candidates, fn ($item) => $item['status'] === 'suggested')), 'rejected' => array_values(array_filter($candidates, fn ($item) => $item['status'] === 'rejected')), 'candidates' => array_values(array_filter($candidates, fn ($item) => $item['status'] !== 'rejected'))];
    }

    private function normaliseUnit(string $unit): ?string
    {
        return match (strtolower(trim($unit))) {
            '' => null, 'gram', 'grams' => 'g', 'kilo', 'kilos', 'kilogram', 'kilograms' => 'kg',
            'liter', 'liters', 'litre', 'litres' => 'l', 'piece', 'pieces', 'pc', 'pcs', 'piraso' => 'pieces',
            'can', 'cans', 'lata' => 'cans', 'pack', 'packs', 'pakete' => 'packs', 'bottle', 'bottles', 'botelya' => 'bottles',
            'box', 'boxes', 'kahon', 'kahons' => 'boxes',
            default => strtolower(trim($unit)),
        };
    }
}
