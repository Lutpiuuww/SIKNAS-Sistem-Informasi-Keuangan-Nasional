<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = DB::table('transactions');
        
        if ($user->role === 'gubernur') {
            $query->where('region_id', $user->region_id);
            $pendapatan = 0; 
            $belanjaPusat = 0; 
            $tkdd = (clone $query)->where('category', 'TKDD')->sum('amount');
            $danaDesa = (clone $query)->where('category', 'Dana Desa')->sum('amount');
        } else {
            $pendapatan = (clone $query)->where('type', 'IN')->sum('amount');
            $belanjaPusat = (clone $query)->where('category', 'Belanja Pusat')->sum('amount');
            $tkdd = (clone $query)->where('category', 'TKDD')->sum('amount');
            $danaDesa = (clone $query)->where('category', 'Dana Desa')->sum('amount');
        }
        
        $anomalies = (clone $query)->where('status', 'Anomaly')->count();
        $pendingCount = (clone $query)->where('status', 'Pending')->count();
        $totalTrx = $query->count();

        $formatT = function($num) { return number_format($num / 1000000000000, 1, ',', '.'); };

        // BI Data: Pie Chart (Expense Distribution)
        $expenseByCategory = (clone $query)->where('type', 'OUT')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')->get();

        // BI Data: Bar Chart (Top 5 Regions)
        $topRegions = DB::table('transactions')
            ->join('regions', 'transactions.region_id', '=', 'regions.id')
            ->where('transactions.type', 'OUT')
            ->select('regions.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('regions.name')
            ->orderBy('total', 'desc')
            ->take(5)->get();

        $data = [
            'pendapatan_t' => $formatT($pendapatan),
            'belanja_pusat_t' => $formatT($belanjaPusat),
            'tkdd_t' => $formatT($tkdd),
            'dana_desa_t' => $formatT($danaDesa),
            'total_daerah_t' => $formatT($tkdd + $danaDesa),
            'anomalies' => $anomalies,
            'pendingCount' => $pendingCount,
            'total_trx' => $totalTrx,
            'user' => $user,
            'expenseByCategory' => $expenseByCategory,
            'topRegions' => $topRegions
        ];

        if ($user->role === 'super_admin') {
            return view('dashboard.super_admin', $data);
        } elseif ($user->role === 'gubernur') {
            return view('dashboard.gubernur', $data);
        } else {
            return view('dashboard.auditor', $data);
        }
    }

    public function reports(Request $request)
    {
        $user = Auth::user();
        $query = DB::table('transactions');
        
        if ($user->role === 'gubernur') {
            $query->where('region_id', $user->region_id);
        }
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('transaction_number', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
            });
        }

        // Summary metrics
        $totalTransactions = (clone $query)->count();
        $totalIn = (clone $query)->where('type', 'IN')->sum('amount');
        $totalOut = (clone $query)->where('type', 'OUT')->sum('amount');

        $transactions = $query->orderBy('created_at', 'desc')->paginate(50);
        return view('reports', compact('transactions', 'user', 'totalTransactions', 'totalIn', 'totalOut'));
    }

    public function exportReports()
    {
        $user = Auth::user();
        $query = DB::table('transactions')->orderBy('created_at', 'desc');
        if ($user->role === 'gubernur') {
            $query->where('region_id', $user->region_id);
        }
        
        $transactions = $query->get();
        
        DB::table('audit_logs')->insert([
            'user_id' => $user->id,
            'action' => 'Mengunduh Dokumen Laporan Transaksi Nasional (CSV).',
            'ip_address' => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $csvData = "No. Transaksi,Waktu,Kategori,Nominal,Tipe,Status\n";
        foreach ($transactions as $trx) {
            $csvData .= "{$trx->transaction_number},{$trx->created_at},{$trx->category},{$trx->amount},{$trx->type},{$trx->status}\n";
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="laporan_transaksi.csv"');
    }

    public function printReport()
    {
        $user = Auth::user();
        $query = DB::table('transactions')->orderBy('created_at', 'desc')->take(100); // Batasi 100 terakhir agar PDF rapi
        if ($user->role === 'gubernur') {
            $query->where('region_id', $user->region_id);
        }
        
        $transactions = $query->get();

        DB::table('audit_logs')->insert([
            'user_id' => $user->id,
            'action' => 'Mencetak Dokumen Laporan Transaksi Nasional Resmi (PDF).',
            'ip_address' => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('print', compact('transactions', 'user'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('laporan_resmi_siknas.pdf');
    }

    public function reportDetail($id)
    {
        $user = Auth::user();
        $query = DB::table('transactions')
            ->leftJoin('regions', 'transactions.region_id', '=', 'regions.id')
            ->select('transactions.*', 'regions.name as region_name', 'regions.type as region_type');

        if ($user->role === 'gubernur') {
            $query->where('transactions.region_id', $user->region_id);
        }

        $trx = $query->where('transactions.id', $id)->first();
        if (!$trx) {
            abort(404, 'Data transaksi tidak ditemukan atau Anda tidak memiliki akses.');
        }

        // Generate Dynamic Breakdown based on Category
        $breakdown = [];
        $total = $trx->amount;

        $regionName = $trx->region_name ?? 'Pusat Nasional';
        
        if (str_contains(strtolower($trx->category), 'pajak')) {
            $breakdown = [
                ['name' => 'Pajak Pertambahan Nilai (PPN)', 'amount' => $total * 0.45, 'purpose' => 'Penerimaan dari transaksi barang & jasa komersial', 'region' => $regionName, 'pic' => 'Dirjen Pajak ' . $regionName],
                ['name' => 'Pajak Penghasilan (PPh) Badan', 'amount' => $total * 0.35, 'purpose' => 'Penerimaan dari laba perusahaan', 'region' => $regionName, 'pic' => 'Kantor Pelayanan Pajak (KPP) ' . $regionName],
                ['name' => 'Pajak Bumi & Bangunan (PBB)', 'amount' => $total * 0.10, 'purpose' => 'Penerimaan dari sektor properti dan perkebunan', 'region' => $regionName, 'pic' => 'Dispenda ' . $regionName],
                ['name' => 'Cukai & Bea Masuk', 'amount' => $total * 0.10, 'purpose' => 'Penerimaan dari impor barang', 'region' => $regionName, 'pic' => 'Dirjen Bea Cukai ' . $regionName],
            ];
        } elseif ($trx->category === 'TKDD' || $trx->category === 'Dana Desa') {
            $breakdown = [
                ['name' => 'Pembangunan Infrastruktur (Jalan & Jembatan)', 'amount' => $total * 0.40, 'purpose' => 'Perbaikan jalan desa sepanjang 15km', 'region' => $regionName, 'pic' => 'Dinas PUPR ' . $regionName],
                ['name' => 'Bantuan Operasional Kesehatan (BOK)', 'amount' => $total * 0.25, 'purpose' => 'Pengadaan obat dan suplemen Posyandu', 'region' => $regionName, 'pic' => 'Dinas Kesehatan ' . $regionName],
                ['name' => 'Bantuan Operasional Sekolah (BOS)', 'amount' => $total * 0.20, 'purpose' => 'Fasilitas pendidikan SD dan SMP', 'region' => $regionName, 'pic' => 'Dinas Pendidikan ' . $regionName],
                ['name' => 'Pemberdayaan UMKM Desa', 'amount' => $total * 0.15, 'purpose' => 'Bantuan modal usaha kelompok tani', 'region' => $regionName, 'pic' => 'Kepala Desa / Perangkat Desa Terkait'],
            ];
        } elseif ($trx->category === 'Belanja Pusat') {
            $breakdown = [
                ['name' => 'Belanja Pegawai (Gaji & Tunjangan)', 'amount' => $total * 0.30, 'purpose' => 'Gaji pokok, tunjangan keluarga ASN', 'region' => 'Nasional', 'pic' => 'KPPN Pusat'],
                ['name' => 'Belanja Barang Operasional', 'amount' => $total * 0.20, 'purpose' => 'ATK, pemeliharaan gedung pemerintahan', 'region' => 'Nasional', 'pic' => 'Biro Umum Kementerian'],
                ['name' => 'Pengadaan Aset Nasional', 'amount' => $total * 0.35, 'purpose' => 'Pembelian kendaraan dinas & alat IT', 'region' => 'Nasional', 'pic' => 'Pejabat Pembuat Komitmen (PPK)'],
                ['name' => 'Subsidi & Bantuan Sosial', 'amount' => $total * 0.15, 'purpose' => 'Subsidi energi dan pupuk', 'region' => 'Nasional', 'pic' => 'Kementerian Sosial & BUMN'],
            ];
        } else {
            $breakdown = [
                ['name' => 'Alokasi Sub-Program A', 'amount' => $total * 0.60, 'purpose' => 'Pelaksanaan tugas pokok spesifik', 'region' => $regionName, 'pic' => 'Direktur Program A'],
                ['name' => 'Alokasi Sub-Program B', 'amount' => $total * 0.40, 'purpose' => 'Dukungan manajemen', 'region' => $regionName, 'pic' => 'Direktur Program B'],
            ];
        }

        return view('report-detail', compact('trx', 'breakdown', 'user'));
    }

    public function geospatial()
    {
        $user = Auth::user();
        $query = DB::table('regions')->orderBy('tkdd_allocated', 'desc');

        if ($user->role === 'gubernur') {
            $query->where('id', $user->region_id);
            $regions = $query->get();
            
            $province = $regions->first();
            $kabupatens = collect();
            
            // Peta Koordinat Asli Kabupaten/Kota per Provinsi
            $realKabs = match ((int)$province->id) {
                1 => [['Kota Banda Aceh', 5.5483, 95.3238], ['Kota Lhokseumawe', 5.1802, 97.1408], ['Kab. Aceh Barat (Meulaboh)', 4.1438, 96.1287]],
                2 => [['Kota Medan', 3.5952, 98.6722], ['Kota Pematangsiantar', 2.9627, 99.0682], ['Kota Sibolga', 1.7410, 98.7788]],
                3 => [['Kota Padang', -0.9492, 100.3543], ['Kota Bukittinggi', -0.3051, 100.3692], ['Kota Solok', -0.7972, 100.6582]],
                4 => [['Kota Pekanbaru', 0.5071, 101.4478], ['Kota Dumai', 1.6815, 101.4435], ['Kab. Bengkalis', 1.4795, 102.1105]],
                5 => [['Kota Jambi', -1.6101, 103.6131], ['Kota Sungai Penuh', -2.0628, 101.3855]],
                6 => [['Kota Palembang', -2.9909, 104.7566], ['Kota Lubuklinggau', -3.2974, 102.8631], ['Kota Prabumulih', -3.4285, 104.2274]],
                7 => [['Kota Bengkulu', -3.7928, 102.2608], ['Kab. Muko-muko', -2.5701, 101.1194]],
                8 => [['Kota Bandar Lampung', -4.3986, 105.2667], ['Kota Metro', -5.1147, 105.3060]],
                9 => [['Kota Pangkal Pinang', -2.1309, 106.1103], ['Kab. Belitung', -2.7369, 107.6358]],
                10 => [['Kota Tanjung Pinang', 0.9168, 104.4608], ['Kota Batam', 1.0828, 104.0305]],
                11 => [['Kota Jakarta Pusat', -6.1865, 106.8341], ['Kota Jakarta Selatan', -6.2615, 106.8106], ['Kota Jakarta Utara', -6.1214, 106.8920]],
                12 => [['Kota Bandung', -6.9175, 107.6191], ['Kota Bekasi', -6.2383, 106.9756], ['Kota Bogor', -6.5950, 106.8166], ['Kota Cirebon', -6.7320, 108.5523]],
                13 => [['Kota Semarang', -6.9667, 110.4167], ['Kota Surakarta', -7.5755, 110.8243], ['Kab. Banyumas', -7.4245, 109.2302]],
                14 => [['Kota Yogyakarta', -7.7956, 110.3695], ['Kab. Sleman', -7.7011, 110.3340], ['Kab. Bantul', -7.8927, 110.3298]],
                15 => [['Kota Surabaya', -7.2504, 112.7688], ['Kota Malang', -7.9666, 112.6326], ['Kota Kediri', -7.8202, 112.0125]],
                16 => [['Kota Serang', -6.1118, 106.1479], ['Kota Tangerang', -6.1702, 106.6403], ['Kota Cilegon', -6.0142, 106.0505]],
                17 => [['Kota Denpasar', -8.6705, 115.2126], ['Kab. Buleleng', -8.1136, 115.0884], ['Kab. Gianyar', -8.5413, 115.3245]],
                18 => [['Kota Mataram', -8.5833, 116.1167], ['Kota Bima', -8.4616, 118.7259], ['Kab. Sumbawa', -8.4975, 117.4290]],
                19 => [['Kota Kupang', -10.1772, 123.6070], ['Kab. Sikka', -8.6255, 122.2152], ['Kab. Ende', -8.8398, 121.6601]],
                20 => [['Kota Pontianak', -0.0263, 109.3425], ['Kota Singkawang', 0.9025, 108.9859], ['Kab. Sintang', 0.0716, 111.4988]],
                21 => [['Kota Palangka Raya', -2.2083, 113.9167], ['Kab. Kotawaringin Timur', -2.5333, 112.9500], ['Kab. Kotawaringin Barat', -2.7333, 111.6167]],
                22 => [['Kota Banjarmasin', -3.3244, 114.5910], ['Kota Banjarbaru', -3.4402, 114.8291], ['Kab. Kotabaru', -3.2384, 116.2346]],
                23 => [['Kota Samarinda', -0.5022, 117.1536], ['Kota Balikpapan', -1.2653, 116.8312], ['Kota Bontang', 0.1343, 117.4795]],
                24 => [['Kab. Bulungan', 2.8364, 117.3688], ['Kota Tarakan', 3.3075, 117.6322], ['Kab. Nunukan', 4.1333, 117.6500]],
                25 => [['Kota Manado', 1.4931, 124.8413], ['Kota Bitung', 1.4452, 125.1818], ['Kota Tomohon', 1.3218, 124.8390]],
                26 => [['Kota Palu', -0.8917, 119.8707], ['Kab. Poso', -1.3965, 120.7516], ['Kab. Banggai', -0.9500, 122.7833]],
                27 => [['Kota Makassar', -5.1476, 119.4327], ['Kota Parepare', -4.0134, 119.6268], ['Kota Palopo', -2.9997, 120.1983]],
                28 => [['Kota Kendari', -3.9985, 122.5126], ['Kota Baubau', -5.4593, 122.5937], ['Kab. Kolaka', -4.0450, 121.6030]],
                29 => [['Kota Gorontalo', 0.5428, 123.0583], ['Kab. Gorontalo', 0.6306, 122.9806]],
                30 => [['Kab. Mamuju', -2.6732, 118.8687], ['Kab. Majene', -3.5421, 118.9701], ['Kab. Polewali Mandar', -3.4216, 119.3402]],
                31 => [['Kota Ambon', -3.6954, 128.1814], ['Kota Tual', -5.6429, 132.7483], ['Kab. Maluku Tengah', -3.3070, 128.9669]],
                32 => [['Kota Ternate', 0.7933, 127.3820], ['Kota Tidore', 0.6865, 127.4264], ['Kab. Halmahera Utara', 1.7333, 128.0000]],
                33 => [['Kab. Manokwari', -0.8615, 134.0620], ['Kab. Fakfak', -2.9234, 132.2965]],
                34 => [['Kota Jayapura', -2.5337, 140.7181], ['Kab. Biak Numfor', -1.1822, 136.0827]],
                35 => [['Kab. Merauke', -8.4975, 140.4022], ['Kab. Boven Digoel', -6.0963, 140.3168]],
                36 => [['Kab. Nabire', -3.3667, 135.5000], ['Kab. Mimika (Timika)', -4.5503, 136.8879]],
                37 => [['Kab. Jayawijaya (Wamena)', -4.0984, 138.9482]],
                38 => [['Kota Sorong', -0.8761, 131.2558], ['Kab. Raja Ampat', -0.2323, 130.5186]],
                default => [['Kab. Utama ' . $province->name, $province->lat + 0.1, $province->lng + 0.1]]
            };
            
            foreach ($realKabs as $index => $kab) {
                $kabupatens->push((object)[
                    'id' => 1000 + $index,
                    'name' => $kab[0],
                    'type' => str_starts_with($kab[0], 'Kota') ? 'Kota' : 'Kabupaten',
                    'tkdd_allocated' => $province->tkdd_allocated / count($realKabs),
                    'lat' => $kab[1],
                    'lng' => $kab[2],
                ]);
            }
            $regions = $regions->merge($kabupatens);
        } else {
            $regions = $query->get();
        }

        return view('geospatial', compact('regions', 'user'));
    }

    public function auditTrail()
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin' && $user->role !== 'auditor') {
            abort(403, 'Anda tidak memiliki otoritas keamanan tingkat lanjut.');
        }

        $logs = DB::table('audit_logs')
            ->join('users', 'audit_logs.user_id', '=', 'users.id')
            ->select('audit_logs.*', 'users.name', 'users.role')
            ->orderBy('audit_logs.created_at', 'desc')
            ->paginate(50);

        return view('audit', compact('logs'));
    }

    public function createTransaction()
    {
        $regions = DB::table('regions')->get();
        return view('transaction-create', compact('regions'));
    }

    public function storeTransaction(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:IN,OUT',
            'description' => 'required|string',
            'region_id' => 'nullable|exists:regions,id',
        ]);

        DB::table('transactions')->insert([
            'transaction_number' => 'TRX-' . strtoupper(uniqid()),
            'category' => $request->category,
            'description' => $request->description,
            'amount' => $request->amount,
            'type' => $request->type,
            'status' => 'Pending', // Fitur Workflow Approval
            'region_id' => $request->region_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('audit_logs')->insert([
            'user_id' => Auth::id(),
            'action' => 'Mengajukan draf transaksi manual (Pending Approval)',
            'ip_address' => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('reports.index')->with('success', 'Transaksi berhasil diajukan dan sedang Menunggu Persetujuan (Pending Approval).');
    }

    public function approvals()
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin') abort(403);
        
        $pending = DB::table('transactions')
            ->leftJoin('regions', 'transactions.region_id', '=', 'regions.id')
            ->select('transactions.*', 'regions.name as region_name')
            ->where('status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('approvals', compact('pending'));
    }

    public function approveTransaction($id, Request $request)
    {
        if (Auth::user()->role !== 'super_admin') abort(403);
        
        $action = $request->input('action'); // 'approve' or 'reject'
        $status = $action === 'approve' ? 'Success' : 'Rejected';
        
        DB::table('transactions')->where('id', $id)->update(['status' => $status, 'updated_at' => now()]);
        DB::table('audit_logs')->insert(['user_id' => Auth::id(), 'action' => 'Melakukan ' . $action . ' pada transaksi ID: ' . $id, 'created_at' => now(), 'updated_at' => now()]);
        
        return back()->with('success', 'Transaksi berhasil di-' . $action);
    }

    // --- MANAJEMEN PENGGUNA ---
    public function users()
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin') {
            abort(403, 'Akses ditolak. Hanya Menteri/Super Admin yang dapat mengelola pengguna.');
        }

        $users = DB::table('users')
            ->leftJoin('regions', 'users.region_id', '=', 'regions.id')
            ->select('users.*', 'regions.name as region_name')
            ->orderBy('users.created_at', 'desc')
            ->get();
            
        $regions = DB::table('regions')->get();
        return view('users', compact('users', 'regions'));
    }

    public function storeUser(Request $request)
    {
        if (Auth::user()->role !== 'super_admin') abort(403);
        
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:super_admin,auditor,gubernur',
            'region_id' => 'nullable|exists:regions,id'
        ]);

        DB::table('users')->insert([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'region_id' => ($request->role === 'gubernur') ? $request->region_id : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('audit_logs')->insert(['user_id' => Auth::id(), 'action' => 'Menambahkan pengguna baru: ' . $request->email, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Pengguna berhasil ditambahkan.');
    }

    // --- MASTER DATA KEMENTERIAN ---
    public function ministries()
    {
        $ministries = DB::table('ministries')->orderBy('budget_allocated', 'desc')->get();
        return view('ministries', compact('ministries'));
    }

    public function ministryDetail($id)
    {
        $ministry = DB::table('ministries')->where('id', $id)->first();
        if (!$ministry) {
            abort(404, 'Kementerian tidak ditemukan.');
        }

        // Generate highly realistic breakdown based on real APBN structures
        $programs = [];
        $total = $ministry->budget_allocated;

        switch($ministry->code) {
            case 'KEMENPUPR':
            case 'KEMEN-PUPR':
                $programs = [
                    ['name' => 'Pembangunan & Preservasi Jalan dan Jembatan', 'amount' => $total * 0.35, 'desc' => 'Infrastruktur konektivitas jalan tol dan non-tol nasional'],
                    ['name' => 'Pengelolaan Sumber Daya Air & Irigasi', 'amount' => $total * 0.28, 'desc' => 'Pembangunan bendungan, jaringan irigasi, dan pengendalian banjir'],
                    ['name' => 'Kawasan Permukiman & Cipta Karya', 'amount' => $total * 0.20, 'desc' => 'Penyediaan air minum, sanitasi, dan infrastruktur permukiman'],
                    ['name' => 'Pembangunan Infrastruktur Dasar IKN', 'amount' => $total * 0.12, 'desc' => 'Dukungan infrastruktur Ibu Kota Nusantara'],
                    ['name' => 'Dukungan Manajemen & Teknis Lainnya', 'amount' => $total * 0.05, 'desc' => 'Belanja operasional dan gaji pegawai'],
                ];
                break;
            case 'KEMENHAN':
                $programs = [
                    ['name' => 'Program Modernisasi Alutsista (Matra Darat, Laut, Udara)', 'amount' => $total * 0.38, 'desc' => 'Pengadaan, pemeliharaan, dan perawatan alat utama sistem senjata'],
                    ['name' => 'Belanja Pegawai dan Kesejahteraan Prajurit TNI', 'amount' => $total * 0.40, 'desc' => 'Gaji, tunjangan kinerja, dan jaminan kesehatan prajurit'],
                    ['name' => 'Dukungan Kesiapan Tempur & Latihan', 'amount' => $total * 0.12, 'desc' => 'Operasi militer selain perang (OMSP) dan latihan gabungan'],
                    ['name' => 'Riset & Teknologi Pertahanan', 'amount' => $total * 0.05, 'desc' => 'Pengembangan industri pertahanan dalam negeri'],
                    ['name' => 'Fasilitas & Pangkalan Militer', 'amount' => $total * 0.05, 'desc' => 'Pembangunan dan perbaikan markas dan perumahan dinas'],
                ];
                break;
            case 'KEMENDIKBUDRISTEK':
                $programs = [
                    ['name' => 'Bantuan Pendidikan (PIP & KIP Kuliah)', 'amount' => $total * 0.35, 'desc' => 'Program Indonesia Pintar dan beasiswa pendidikan tinggi'],
                    ['name' => 'Program Bantuan Operasional Sekolah (BOS)', 'amount' => $total * 0.30, 'desc' => 'Dana transfer daerah untuk operasional sekolah dasar hingga menengah'],
                    ['name' => 'Tunjangan Profesi Guru & Dosen', 'amount' => $total * 0.20, 'desc' => 'Sertifikasi dan tunjangan kinerja pendidik'],
                    ['name' => 'Program Kampus Merdeka & Riset Inovasi', 'amount' => $total * 0.10, 'desc' => 'Dana padanan (matching fund) dan riset perguruan tinggi'],
                    ['name' => 'Pemajuan Kebudayaan & Bahasa', 'amount' => $total * 0.05, 'desc' => 'Pelestarian cagar budaya dan pengembangan bahasa asing'],
                ];
                break;
            case 'KEMENKES':
                $programs = [
                    ['name' => 'Penerima Bantuan Iuran (PBI) JKN', 'amount' => $total * 0.45, 'desc' => 'Subsidi BPJS Kesehatan bagi masyarakat miskin (Data Terpadu Kesejahteraan Sosial)'],
                    ['name' => 'Transformasi Layanan Primer (Puskesmas/Posyandu)', 'amount' => $total * 0.20, 'desc' => 'Peralatan medis, imunisasi rutin, dan penurunan stunting'],
                    ['name' => 'Transformasi Layanan Rujukan (RSUD)', 'amount' => $total * 0.15, 'desc' => 'Pembangunan dan peningkatan fasilitas rumah sakit vertikal'],
                    ['name' => 'Ketahanan Farmasi & Alat Kesehatan', 'amount' => $total * 0.10, 'desc' => 'Pengadaan obat esensial dan kemandirian farmasi'],
                    ['name' => 'Belanja Pegawai & Insentif Nakes', 'amount' => $total * 0.10, 'desc' => 'Gaji tenaga kesehatan dan dokter spesialis'],
                ];
                break;
            case 'KEMENSOS':
                $programs = [
                    ['name' => 'Program Keluarga Harapan (PKH)', 'amount' => $total * 0.45, 'desc' => 'Bantuan tunai bersyarat untuk keluarga kurang mampu'],
                    ['name' => 'Program Sembako / Bantuan Pangan Non-Tunai (BPNT)', 'amount' => $total * 0.40, 'desc' => 'Bantuan kebutuhan pokok masyarakat miskin'],
                    ['name' => 'Rehabilitasi Sosial (ATENSI)', 'amount' => $total * 0.08, 'desc' => 'Asistensi rehabilitasi anak yatim piatu, lansia, dan disabilitas'],
                    ['name' => 'Pemberdayaan Sosial', 'amount' => $total * 0.04, 'desc' => 'Pemberdayaan Komunitas Adat Terpencil (KAT) dan pahlawan'],
                    ['name' => 'Manajemen Operasional & Penyaluran', 'amount' => $total * 0.03, 'desc' => 'Biaya administrasi penyaluran dana bantuan sosial'],
                ];
                break;
            default:
                $programs = [
                    ['name' => 'Belanja Pegawai (Gaji & Tunjangan)', 'amount' => $total * 0.35, 'desc' => 'Komponen gaji rutin, tunjangan kinerja ASN dan PPNPN'],
                    ['name' => 'Belanja Barang Operasional', 'amount' => $total * 0.25, 'desc' => 'Biaya operasional kantor, pemeliharaan aset, dan perjalanan dinas'],
                    ['name' => 'Pelaksanaan Program Prioritas Instansi', 'amount' => $total * 0.30, 'desc' => 'Eksekusi program kerja teknis kementerian sesuai RPJMN'],
                    ['name' => 'Pengadaan Aset / Belanja Modal', 'amount' => $total * 0.10, 'desc' => 'Pembangunan infrastruktur atau pengadaan IT internal'],
                ];
                break;
        }

        return view('ministry-detail', compact('ministry', 'programs'));
    }
}
