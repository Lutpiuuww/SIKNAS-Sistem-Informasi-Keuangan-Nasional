<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\Models\User::where('email', 'menkeu@siknas.gov')->first();
echo Hash::check('Menkeu2026!', $u->password) ? 'MATCH' : 'NO_MATCH';
