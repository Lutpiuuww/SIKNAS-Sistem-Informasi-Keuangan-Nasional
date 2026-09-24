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

        return view('welcome', [
            'pendapatan_t' => $formatT($pendapatan),
            'belanja_pusat_t' => $formatT($belanjaPusat),
            'tkdd_t' => $formatT($tkdd),
            'dana_desa_t' => $formatT($danaDesa),
            'anomalies' => $anomalies,
            'pendingCount' => $pendingCount,
            'total_trx' => $totalTrx,
            'user' => $user,
            'expenseByCategory' => $expenseByCategory,
            'topRegions' => $topRegions
        ]);
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

        if (str_contains(strtolower($trx->category), 'pajak')) {
            $breakdown = [
                ['name' => 'Pajak Pertambahan Nilai (PPN)', 'amount' => $total * 0.45],
                ['name' => 'Pajak Penghasilan (PPh) Badan', 'amount' => $total * 0.35],
                ['name' => 'Pajak Bumi & Bangunan (PBB)', 'amount' => $total * 0.10],
                ['name' => 'Cukai & Bea Masuk', 'amount' => $total * 0.10],
            ];
        } elseif ($trx->category === 'TKDD' || $trx->category === 'Dana Desa') {
            $breakdown = [
                ['name' => 'Pembangunan Infrastruktur (Jalan & Jembatan)', 'amount' => $total * 0.40],
                ['name' => 'Bantuan Operasional Kesehatan (BOK)', 'amount' => $total * 0.25],
                ['name' => 'Bantuan Operasional Sekolah (BOS)', 'amount' => $total * 0.20],
                ['name' => 'Pemberdayaan UMKM Desa', 'amount' => $total * 0.15],
            ];
        } elseif ($trx->category === 'Belanja Pusat') {
            $breakdown = [
                ['name' => 'Belanja Pegawai (Gaji & Tunjangan)', 'amount' => $total * 0.30],
                ['name' => 'Belanja Barang Operasional', 'amount' => $total * 0.20],
                ['name' => 'Pengadaan Aset Nasional', 'amount' => $total * 0.35],
                ['name' => 'Subsidi & Bantuan Sosial', 'amount' => $total * 0.15],
            ];
        } else {
            $breakdown = [
                ['name' => 'Alokasi Sub-Program A', 'amount' => $total * 0.60],
                ['name' => 'Alokasi Sub-Program B', 'amount' => $total * 0.40],
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
        }

        $regions = $query->get();
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
