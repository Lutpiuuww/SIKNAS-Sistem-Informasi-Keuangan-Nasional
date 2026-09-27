<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\Models\User::where('email', 'gubernur_aceh@siknas.gov')->first();
if ($u) {
    echo "Email: " . $u->email . "\n";
    echo "Hash: " . $u->password . "\n";
    echo "Match Password 'GubernurAceh2026!': " . (Illuminate\Support\Facades\Hash::check('GubernurAceh2026!', $u->password) ? 'YES' : 'NO');
} else {
    echo "USER NOT FOUND";
}
