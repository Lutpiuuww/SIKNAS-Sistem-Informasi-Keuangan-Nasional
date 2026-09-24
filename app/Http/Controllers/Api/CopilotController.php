<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CopilotController extends Controller
{
    public function chat(Request $request)
    {
        $message = $request->input('message');
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json([
                'reply' => "⚠️ GEMINI_API_KEY belum dikonfigurasi di file .env Anda. Saat ini saya berjalan dalam mode offline.\n\nSistem AI Sentinel merekomendasikan untuk menambahkan API Key agar saya dapat menganalisis data SIKNAS Anda secara real-time."
            ]);
        }

        // Kumpulkan konteks data terkini untuk dikirim ke Gemini
        $totalIn = DB::table('transactions')->where('type', 'IN')->sum('amount');
        $totalOut = DB::table('transactions')->where('type', 'OUT')->sum('amount');
        $anomalies = DB::table('transactions')->where('status', 'Anomaly')->count();
        $pending = DB::table('transactions')->where('status', 'Pending')->count();

        $formatRp = function($num) { return 'Rp ' . number_format($num / 1e12, 2, ',', '.') . ' Triliun'; };

        $context = "Anda adalah Sentinel AI, asisten sistem keuangan nasional (SIKNAS). Jawab dengan format Markdown ringkas, profesional, ala konsultan. "
            . "Data saat ini: Total Pemasukan: " . $formatRp($totalIn) . ", Total Pengeluaran: " . $formatRp($totalOut) . ". "
            . "Jumlah transaksi anomali (mencurigakan): $anomalies. "
            . "Menunggu persetujuan (pending): $pending. "
            . "Pengguna yang bertanya: " . Auth::user()->name . " (Role: " . Auth::user()->role . ").\n\n"
            . "Pertanyaan: $message";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=' . $apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $context]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $reply = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? "Maaf, terjadi kesalahan pemrosesan AI.";
                return response()->json(['reply' => $reply]);
            }

            return response()->json([
                'reply' => "Gagal terhubung ke server AI Gemini. " . $response->body()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'reply' => "Error: " . $e->getMessage()
            ]);
        }
    }
}
