<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class Sentinel
{
    public static function detectAnomaly($category, $amount)
    {
        // Ambil 500 transaksi terakhir untuk kategori ini
        $history = DB::table('transactions')
            ->where('category', $category)
            ->where('type', 'OUT') // Hanya pengeluaran yang dideteksi
            ->orderBy('id', 'desc')
            ->take(500)
            ->pluck('amount');

        if ($history->count() < 10) {
            // Belum cukup data historis, pakai baseline default (500 Miliar)
            return $amount > 500000000000; 
        }

        $mean = $history->avg();
        
        $variance = 0.0;
        foreach($history as $i) {
            $variance += pow($i - $mean, 2);
        }
        $variance /= $history->count();
        $stdDev = sqrt($variance);

        // Jika transaksi lebih besar dari rata-rata + 2 * Standar Deviasi (Z-Score > 2)
        // Maka itu adalah anomali
        return $amount > ($mean + (2 * $stdDev));
    }
}
