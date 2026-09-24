<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StreamTransactions extends Command
{
    protected $signature = 'siknas:stream';
    protected $description = 'Auto-generate transactions endlessly for the live command center';

    public function handle()
    {
        $this->info("Memulai simulasi arus kas nasional... Tekan Ctrl+C untuk berhenti.");
        $categories = ['Pendapatan Pajak', 'Pendapatan Non-Pajak', 'Belanja Pusat', 'TKDD', 'Dana Desa', 'Lelang SBN'];

        while (true) {
            $cat = $categories[array_rand($categories)];
            $isIncome = in_array($cat, ['Pendapatan Pajak', 'Pendapatan Non-Pajak', 'Lelang SBN']);
            $amount = rand(5000000000, 500000000000); 
            
            $regionId = in_array($cat, ['TKDD', 'Dana Desa']) ? rand(1, 38) : null;
            $isAnomaly = (!$isIncome && $amount > 300000000000);
            
            DB::table('transactions')->insert([
                'transaction_number' => 'TRX-' . strtoupper(uniqid()),
                'category' => $cat,
                'description' => 'Live Stream: ' . $cat,
                'amount' => $amount,
                'type' => $isIncome ? 'IN' : 'OUT',
                'status' => $isAnomaly ? 'Anomaly' : 'Success',
                'region_id' => $regionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $statusText = $isAnomaly ? '<fg=red>ANOMALI</>' : '<fg=green>SUCCESS</>';
            $this->line("[" . now()->format('H:i:s') . "] $statusText - $cat (Rp " . number_format($amount) . ")");
            
            // Wait between 0.5 to 2 seconds
            usleep(rand(500000, 2000000));
        }
    }
}
