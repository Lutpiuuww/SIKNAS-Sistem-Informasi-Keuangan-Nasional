<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('role', 'super_admin')->first();
Auth::login($user);

$viewsToTest = [
    ['view' => 'reports', 'data' => ['transactions' => App\Models\Transaction::paginate(10), 'user' => $user, 'totalTransactions' => 0, 'totalIn' => 0, 'totalOut' => 0]],
    ['view' => 'print', 'data' => ['transactions' => App\Models\Transaction::take(10)->get(), 'user' => $user]],
    ['view' => 'report-detail', 'data' => ['trx' => App\Models\Transaction::first(), 'breakdown' => [], 'user' => $user]],
    ['view' => 'geospatial', 'data' => ['regions' => App\Models\Region::all(), 'user' => $user]],
    ['view' => 'audit', 'data' => ['logs' => Illuminate\Support\Facades\DB::table('audit_logs')->paginate(10)]],
    ['view' => 'transaction-create', 'data' => ['regions' => App\Models\Region::all()]],
    ['view' => 'approvals', 'data' => ['pending' => App\Models\Transaction::where('status', 'Pending')->get()]],
    ['view' => 'users', 'data' => ['users' => App\Models\User::all(), 'regions' => App\Models\Region::all()]],
    ['view' => 'ministries', 'data' => ['ministries' => Illuminate\Support\Facades\DB::table('ministries')->get()]],
    ['view' => 'ministry-detail', 'data' => ['ministry' => Illuminate\Support\Facades\DB::table('ministries')->first(), 'programs' => []]],
];

foreach ($viewsToTest as $item) {
    try {
        $html = view($item['view'], $item['data'])->render();
        echo $item['view'] . " OK\n";
    } catch(\Exception $e) {
        echo $item['view'] . " ERROR: " . $e->getMessage() . "\n";
    }
}
