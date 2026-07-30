<?php

namespace App\Http\Controllers;

use App\Services\PantryInputService;
use Illuminate\Http\Request;

class PantryInputController extends Controller
{
    public function barcode(Request $request, PantryInputService $inputs)
    {
        $data = $request->validate(['barcode' => 'required|string|min:6|max:32']);
        return response()->json($inputs->fromBarcode($data['barcode']));
    }

    public function voice(Request $request, PantryInputService $inputs)
    {
        return response()->json($inputs->fromVoice($request->validate([
            'transcript' => 'required|string|min:2|max:5000',
        ])['transcript']));
    }

    public function receipt(Request $request, PantryInputService $inputs)
    {
        $data = $request->validate([
            'receipt' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'recognized_text' => 'nullable|string|max:10000',
        ]);
        return response()->json($inputs->fromReceipt($data['receipt'], $data['recognized_text'] ?? null), 201);
    }

    public function receiptText(Request $request, PantryInputService $inputs)
    {
        $text = $request->validate(['text' => 'required|string|min:2|max:10000'])['text'];
        return response()->json($inputs->fromText($text, 'receipt'));
    }
}
