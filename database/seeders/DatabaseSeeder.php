<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Seed User
        \App\Models\User::factory()->create([
            'name' => 'Menteri Keuangan',
            'email' => 'menteri@siknas.gov',
            'password' => bcrypt('password'),
            'role' => 'super_admin'
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Auditor Utama',
            'email' => 'auditor@siknas.gov',
            'password' => bcrypt('password'),
            'role' => 'auditor'
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Gubernur Jawa Barat',
            'email' => 'gubernur@siknas.gov',
            'password' => bcrypt('password'),
            'role' => 'gubernur',
            'region_id' => 12 // ID Provinsi Jawa Barat is 12, not 1!
        ]);

        // 4. Seed Ministries (Full List of Indonesian Ministries)
        $ministries = [
            ['Kemenko Polhukam', 'KEMENKO-POLHUKAM', 25000000000000],
            ['Kemenko Perekonomian', 'KEMENKO-EKON', 18000000000000],
            ['Kemenko PMK', 'KEMENKO-PMK', 21000000000000],
            ['Kemenko Marves', 'KEMENKO-MARVES', 30000000000000],
            ['Kementerian Dalam Negeri', 'KEMENDAGRI', 45000000000000],
            ['Kementerian Luar Negeri', 'KEMENLU', 8000000000000],
            ['Kementerian Pertahanan', 'KEMENHAN', 135000000000000],
            ['Kementerian Agama', 'KEMENAG', 70000000000000],
            ['Kementerian Hukum dan HAM', 'KEMENKUMHAM', 15000000000000],
            ['Kementerian Keuangan', 'KEMENKEU', 45000000000000],
            ['Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi', 'KEMENDIKBUDRISTEK', 85000000000000],
            ['Kementerian Kesehatan', 'KEMENKES', 90000000000000],
            ['Kementerian Sosial', 'KEMENSOS', 78000000000000],
            ['Kementerian Ketenagakerjaan', 'KEMNAKER', 9000000000000],
            ['Kementerian Perindustrian', 'KEMENPERIN', 5000000000000],
            ['Kementerian Perdagangan', 'KEMENDAG', 3500000000000],
            ['Kementerian ESDM', 'KEMEN-ESDM', 12000000000000],
            ['Kementerian PUPR', 'KEMEN-PUPR', 150000000000000],
            ['Kementerian Perhubungan', 'KEMENHUB', 35000000000000],
            ['Kementerian Kominfo', 'KEMENKOMINFO', 20000000000000],
            ['Kementerian Pertanian', 'KEMENTAN', 15000000000000],
            ['Kementerian Lingkungan Hidup dan Kehutanan', 'KLHK', 8000000000000],
            ['Kementerian Kelautan dan Perikanan', 'KKP', 6000000000000],
            ['Kementerian Desa, PDT, dan Transmigrasi', 'KEMENDES-PDTT', 4500000000000],
            ['Kementerian ATR/BPN', 'KEMEN-ATR', 9000000000000],
            ['Kementerian PPN/Bappenas', 'BAPPENAS', 2500000000000],
            ['Kementerian PANRB', 'KEMENPAN-RB', 1500000000000],
            ['Kementerian BUMN', 'KEMEN-BUMN', 1200000000000],
            ['Kementerian Koperasi dan UKM', 'KEMENKOP-UKM', 2800000000000],
            ['Kementerian Pariwisata dan Ekonomi Kreatif', 'KEMENPAREKRAF', 4500000000000],
            ['Kementerian PPPA', 'KEMEN-PPPA', 1800000000000],
            ['Kementerian Pemuda dan Olahraga', 'KEMENPORA', 2500000000000],
            ['Kementerian Investasi/BKPM', 'BKPM', 1500000000000],
        ];

        foreach ($ministries as $m) {
            DB::table('ministries')->insert([
                'name' => $m[0],
                'code' => $m[1],
                'budget_allocated' => $m[2],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Seed 38 Regions (Provinsi)
        $regions = [
            ['id'=>1, 'name'=>'Aceh', 'type'=>'Provinsi', 'tkdd'=>30000000000000, 'lat'=>4.695135, 'lng'=>96.749399],
            ['id'=>2, 'name'=>'Sumatera Utara', 'type'=>'Provinsi', 'tkdd'=>35000000000000, 'lat'=>2.115355, 'lng'=>99.545097],
            ['id'=>3, 'name'=>'Sumatera Barat', 'type'=>'Provinsi', 'tkdd'=>20000000000000, 'lat'=>-0.739940, 'lng'=>100.800005],
            ['id'=>4, 'name'=>'Riau', 'type'=>'Provinsi', 'tkdd'=>25000000000000, 'lat'=>0.293347, 'lng'=>101.706829],
            ['id'=>5, 'name'=>'Jambi', 'type'=>'Provinsi', 'tkdd'=>15000000000000, 'lat'=>-1.611572, 'lng'=>103.613120],
            ['id'=>6, 'name'=>'Sumatera Selatan', 'type'=>'Provinsi', 'tkdd'=>22000000000000, 'lat'=>-3.319437, 'lng'=>103.914399],
            ['id'=>7, 'name'=>'Bengkulu', 'type'=>'Provinsi', 'tkdd'=>10000000000000, 'lat'=>-3.792845, 'lng'=>102.260764],
            ['id'=>8, 'name'=>'Lampung', 'type'=>'Provinsi', 'tkdd'=>20000000000000, 'lat'=>-4.558585, 'lng'=>105.406808],
            ['id'=>9, 'name'=>'Kepulauan Bangka Belitung', 'type'=>'Provinsi', 'tkdd'=>9000000000000, 'lat'=>-2.741051, 'lng'=>106.440587],
            ['id'=>10, 'name'=>'Kepulauan Riau', 'type'=>'Provinsi', 'tkdd'=>12000000000000, 'lat'=>3.945651, 'lng'=>108.142867],
            ['id'=>11, 'name'=>'DKI Jakarta', 'type'=>'Provinsi', 'tkdd'=>45000000000000, 'lat'=>-6.208763, 'lng'=>106.845599],
            ['id'=>12, 'name'=>'Jawa Barat', 'type'=>'Provinsi', 'tkdd'=>40000000000000, 'lat'=>-6.917464, 'lng'=>107.619123],
            ['id'=>13, 'name'=>'Jawa Tengah', 'type'=>'Provinsi', 'tkdd'=>38000000000000, 'lat'=>-7.150975, 'lng'=>110.140259],
            ['id'=>14, 'name'=>'DI Yogyakarta', 'type'=>'Provinsi', 'tkdd'=>15000000000000, 'lat'=>-7.795580, 'lng'=>110.369490],
            ['id'=>15, 'name'=>'Jawa Timur', 'type'=>'Provinsi', 'tkdd'=>39000000000000, 'lat'=>-7.250445, 'lng'=>112.768845],
            ['id'=>16, 'name'=>'Banten', 'type'=>'Provinsi', 'tkdd'=>18000000000000, 'lat'=>-6.405817, 'lng'=>106.064018],
            ['id'=>17, 'name'=>'Bali', 'type'=>'Provinsi', 'tkdd'=>14000000000000, 'lat'=>-8.409518, 'lng'=>115.188919],
            ['id'=>18, 'name'=>'Nusa Tenggara Barat', 'type'=>'Provinsi', 'tkdd'=>15000000000000, 'lat'=>-8.652933, 'lng'=>117.361648],
            ['id'=>19, 'name'=>'Nusa Tenggara Timur', 'type'=>'Provinsi', 'tkdd'=>16000000000000, 'lat'=>-8.622420, 'lng'=>121.079370],
            ['id'=>20, 'name'=>'Kalimantan Barat', 'type'=>'Provinsi', 'tkdd'=>17000000000000, 'lat'=>-0.278781, 'lng'=>111.475285],
            ['id'=>21, 'name'=>'Kalimantan Tengah', 'type'=>'Provinsi', 'tkdd'=>15000000000000, 'lat'=>-1.681488, 'lng'=>113.382355],
            ['id'=>22, 'name'=>'Kalimantan Selatan', 'type'=>'Provinsi', 'tkdd'=>14000000000000, 'lat'=>-3.092642, 'lng'=>115.283758],
            ['id'=>23, 'name'=>'Kalimantan Timur', 'type'=>'Provinsi', 'tkdd'=>16000000000000, 'lat'=>0.538659, 'lng'=>116.419389],
            ['id'=>24, 'name'=>'Kalimantan Utara', 'type'=>'Provinsi', 'tkdd'=>10000000000000, 'lat'=>3.073090, 'lng'=>116.041390],
            ['id'=>25, 'name'=>'Sulawesi Utara', 'type'=>'Provinsi', 'tkdd'=>12000000000000, 'lat'=>0.624693, 'lng'=>123.975002],
            ['id'=>26, 'name'=>'Sulawesi Tengah', 'type'=>'Provinsi', 'tkdd'=>13000000000000, 'lat'=>-1.430025, 'lng'=>121.445618],
            ['id'=>27, 'name'=>'Sulawesi Selatan', 'type'=>'Provinsi', 'tkdd'=>18000000000000, 'lat'=>-3.668799, 'lng'=>119.974053],
            ['id'=>28, 'name'=>'Sulawesi Tenggara', 'type'=>'Provinsi', 'tkdd'=>11000000000000, 'lat'=>-4.144910, 'lng'=>122.174605],
            ['id'=>29, 'name'=>'Gorontalo', 'type'=>'Provinsi', 'tkdd'=>8000000000000, 'lat'=>0.699937, 'lng'=>122.446724],
            ['id'=>30, 'name'=>'Sulawesi Barat', 'type'=>'Provinsi', 'tkdd'=>7000000000000, 'lat'=>-2.844137, 'lng'=>119.231278],
            ['id'=>31, 'name'=>'Maluku', 'type'=>'Provinsi', 'tkdd'=>9000000000000, 'lat'=>-3.238462, 'lng'=>130.145273],
            ['id'=>32, 'name'=>'Maluku Utara', 'type'=>'Provinsi', 'tkdd'=>8000000000000, 'lat'=>1.570999, 'lng'=>127.808769],
            ['id'=>33, 'name'=>'Papua Barat', 'type'=>'Provinsi', 'tkdd'=>11000000000000, 'lat'=>-1.336115, 'lng'=>133.174716],
            ['id'=>34, 'name'=>'Papua', 'type'=>'Provinsi', 'tkdd'=>15000000000000, 'lat'=>-4.269928, 'lng'=>138.080353],
            ['id'=>35, 'name'=>'Papua Selatan', 'type'=>'Provinsi', 'tkdd'=>5000000000000, 'lat'=>-7.000000, 'lng'=>139.000000],
            ['id'=>36, 'name'=>'Papua Tengah', 'type'=>'Provinsi', 'tkdd'=>6000000000000, 'lat'=>-4.000000, 'lng'=>136.000000],
            ['id'=>37, 'name'=>'Papua Pegunungan', 'type'=>'Provinsi', 'tkdd'=>5000000000000, 'lat'=>-4.000000, 'lng'=>139.000000],
            ['id'=>38, 'name'=>'Papua Barat Daya', 'type'=>'Provinsi', 'tkdd'=>4000000000000, 'lat'=>-1.000000, 'lng'=>132.000000],
        ];

        foreach($regions as $r) {
            DB::table('regions')->insert([
                'id' => $r['id'], 'name' => $r['name'], 'type' => $r['type'], 'tkdd_allocated' => $r['tkdd'],
                'lat' => $r['lat'], 'lng' => $r['lng'],
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // 3. Seed Transactions (Big Data Simulation)
        $transactions = [];
        $categories = ['Pendapatan Pajak', 'Pendapatan Non-Pajak', 'Belanja Pusat', 'TKDD', 'Dana Desa', 'Lelang SBN'];
        
        for($i=0; $i<200; $i++) {
            $cat = $categories[array_rand($categories)];
            $isIncome = in_array($cat, ['Pendapatan Pajak', 'Pendapatan Non-Pajak', 'Lelang SBN']);
            $amount = rand(10000000000, 5000000000000);
            
            // Tautkan transaksi TKDD dan Dana Desa ke region_id secara acak (1-38)
            $regionId = in_array($cat, ['TKDD', 'Dana Desa']) ? rand(1, 38) : null;

            $transactions[] = [
                'transaction_number' => 'TRX-' . strtoupper(uniqid()),
                'category' => $cat,
                'description' => 'Simulasi ' . $cat,
                'amount' => $amount,
                'type' => $isIncome ? 'IN' : 'OUT',
                'status' => (!$isIncome && $amount > 3000000000000) ? 'Anomaly' : 'Success',
                'region_id' => $regionId,
                'created_at' => Carbon::now()->subDays(rand(0, 30))->subMinutes(rand(0, 1440)),
                'updated_at' => now(),
            ];
        }
        
        DB::table('transactions')->insert($transactions);
    }
}
