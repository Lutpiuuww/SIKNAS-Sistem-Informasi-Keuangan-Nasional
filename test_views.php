<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach(['super_admin', 'gubernur', 'auditor'] as $role) {
    try {
        $user = App\Models\User::where('role', $role)->first();
        Auth::login($user);
        $html = view('dashboard.' . $role, [
            'user' => $user, 
            'tkdd_t' => '1,5', 
            'dana_desa_t' => '0,5', 
            'pendapatan_t' => '0', 
            'belanja_pusat_t' => '0', 
            'anomalies' => 0, 
            'pendingCount' => 0, 
            'total_trx' => 0, 
            'expenseByCategory' => collect([]), 
            'topRegions' => collect([])
        ])->render();
        echo "$role view OK\n";
    } catch(\Exception $e) {
        echo "$role view ERROR: " . $e->getMessage() . "\n";
    }
}
