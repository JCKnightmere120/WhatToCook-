<?php

namespace App\Http\Controllers;

use App\Models\ShoppingList;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    /**
     * Export the user's unpurchased shopping list items as a plain text file.
     */
    public function text(Request $request)
    {
        $items = $this->unpurchasedItems($request);

        $lines = ["WhatToCook - Shopping List", 'Generated: '.now()->format('Y-m-d H:i'), ''];

        if ($items->isEmpty()) {
            $lines[] = 'Wala nay laing gikinahanglan — kompleto na ang pantry!';
        } else {
            foreach ($items as $item) {
                $qty = trim(($item->quantity ?? '').' '.($item->unit ?? ''));
                $lines[] = '- '.$item->ingredient_name.($qty ? " ({$qty})" : '');
            }
        }

        $content = implode("\n", $lines);

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="shopping-list.txt"',
        ]);
    }

    /**
     * Export the user's unpurchased shopping list items as a PDF.
     */
    public function pdf(Request $request)
    {
        $items = $this->unpurchasedItems($request);

        $pdf = Pdf::loadView('pdf.shopping-list', [
            'items' => $items,
            'userName' => $request->user()->name,
            'generatedAt' => now()->format('Y-m-d H:i'),
        ]);

        return $pdf->download('shopping-list.pdf');
    }

    /**
     * Export the user's unpurchased shopping list items as a PNG image.
     */
    public function image(Request $request)
    {
        $items = $this->unpurchasedItems($request);

        $lineHeight = 26;
        $padding = 30;
        $width = 500;
        $height = $padding * 2 + $lineHeight * (max($items->count(), 1) + 2);

        $img = imagecreatetruecolor($width, $height);

        $cream = imagecolorallocate($img, 250, 240, 220);
        $brown = imagecolorallocate($img, 42, 24, 16);
        $orange = imagecolorallocate($img, 217, 122, 77);
        $muted = imagecolorallocate($img, 138, 122, 104);

        imagefill($img, 0, 0, $cream);

        $font = 5; // built-in GD font, no external font file needed

        imagestring($img, $font, $padding, $padding, 'WhatToCook - Shopping List', $orange);
        imagestring($img, 3, $padding, $padding + 20, 'Generated: '.now()->format('Y-m-d H:i'), $muted);

        $y = $padding + 45;

        if ($items->isEmpty()) {
            imagestring($img, $font, $padding, $y, 'Wala nay laing gikinahanglan!', $muted);
        } else {
            foreach ($items as $item) {
                $qty = trim(($item->quantity ?? '').' '.($item->unit ?? ''));
                $line = '- '.$item->ingredient_name.($qty ? " ({$qty})" : '');
                imagestring($img, $font, $padding, $y, $line, $brown);
                $y += $lineHeight;
            }
        }

        ob_start();
        imagepng($img);
        $imageData = ob_get_clean();
        imagedestroy($img);

        return response($imageData, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="shopping-list.png"',
        ]);
    }

    private function unpurchasedItems(Request $request)
    {
        return ShoppingList::where('user_id', $request->user()->id)
            ->where('is_purchased', false)
            ->orderBy('ingredient_name')
            ->get();
    }
}