<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// Models
use App\Models\EncryptLog;
use App\Models\DecryptLog;

// Services
use App\Services\WidalService;

class WidalController extends Controller
{
    protected WidalService $widal;
    
    public function __construct(WidalService $widal)
    {
        $this->widal = $widal;
    }
    
    // Transformasi Bahasa Widal
    public function transform(Request $request)
    {
        $data = $request->validate([
            'text' => ['required', 'string'],
            'mode' => ['required', Rule::in(['to_widal', 'from_widal'])],
            'reversal' => ['sometimes', 'boolean'],
        ]);

        // Ekstrak data
        $text = $data['text'];
        $mode = $data['mode'];
        $reversal = $data['reversal'] ?? false;

        // Transformasi ke Widal
        if ($mode === 'to_widal') {
            $out = $this->widal->encode($text, $reversal);

            $payload = [
                'result' => $out,
                'mode' => $mode,
            ];
            EncryptLog::create([
                'text' => $text,
                'result' => $out,
                'mode' => $mode,
            ]);

            return response()->json($payload);
        } elseif ($mode === 'from_widal') {
            $out = $this->widal->decode($text);

            $payload = [
                'result' => $out,
                'mode' => $mode,
            ];
            DecryptLog::create([
                'text' => $text,
                'result' => $out,
                'mode' => $mode,
            ]);

            return response()->json($payload);
        }
    }
}
