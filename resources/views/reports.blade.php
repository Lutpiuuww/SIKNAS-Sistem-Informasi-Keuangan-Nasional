@extends('layouts.app')
@section('title', 'Laporan Keuangan')

@section('content')
<header class="max-w-7xl mx-auto mb-8 mt-8 gsap-fade">
    <h1 class="text-4xl font-bold tracking-tight mb-3">Laporan Transaksi</h1>
    @if($user->role === 'gubernur')
        <p class="text-textSecondary text-lg">Catatan historis aliran dana khusus untuk wilayah Anda.</p>
    @else
        <p class="text-textSecondary text-lg">Catatan historis seluruh aliran dana negara.</p>
    @endif
</header>
<main class="max-w-7xl mx-auto mb-12 gsap-fade">
    
    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bento-card p-6 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-900/30 text-brandBlue flex items-center justify-center text-xl"><i class="ph-bold ph-receipt"></i></div>
            <div>
                <div class="text-sm text-textSecondary font-medium">Total Dokumen</div>
                <div class="text-2xl font-bold text-textPrimary dark:text-darkText" id="metric-total-doc">{{ number_format($totalTransactions) }}</div>
            </div>
        </div>
        <div class="bento-card p-6 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-green-50 dark:bg-green-900/30 text-accentGreen flex items-center justify-center text-xl"><i class="ph-bold ph-arrow-down-left"></i></div>
            <div>
                <div class="text-sm text-textSecondary font-medium">Kas Masuk (IN)</div>
                <div class="text-2xl font-bold text-textPrimary dark:text-darkText">Rp <span id="metric-total-in">{{ number_format($totalIn / 1e9, 1, ',', '.') }}</span> M</div>
            </div>
        </div>
        <div class="bento-card p-6 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-red-50 dark:bg-red-900/30 text-accentRed flex items-center justify-center text-xl"><i class="ph-bold ph-arrow-up-right"></i></div>
            <div>
                <div class="text-sm text-textSecondary font-medium">Kas Keluar (OUT)</div>
                <div class="text-2xl font-bold text-textPrimary dark:text-darkText">Rp <span id="metric-total-out">{{ number_format($totalOut / 1e9, 1, ',', '.') }}</span> M</div>
            </div>
        </div>
    </div>

    <div class="bento-card p-8">

    <div class="flex justify-between items-center mb-6">
        <form method="GET" action="{{ route('reports.index') }}" class="flex gap-2 w-1/2">
            <input type="text" name="search" placeholder="Cari transaksi atau kategori..." value="{{ request('search') }}" class="w-full bg-gray-50 dark:bg-darkBg border border-gray-200 dark:border-gray-700 px-4 py-2 rounded-xl text-sm focus:outline-none focus:border-brandBlue transition-colors text-textPrimary dark:text-darkText">
            <button type="submit" class="bg-brandBlue text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors">Cari</button>
            @if(request()->filled('search'))
                <a href="{{ route('reports.index') }}" class="px-4 py-2 text-textSecondary text-sm font-medium hover:text-textPrimary transition-colors flex items-center">Reset</a>
            @endif
        </form>
        <div class="flex gap-2">
            <a href="{{ route('reports.create') }}" class="flex items-center gap-2 bg-textPrimary dark:bg-brandBlue text-white hover:bg-black dark:hover:bg-blue-700 px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                <i class="ph-bold ph-plus"></i> Entri Manual
            </a>
            <a href="{{ route('reports.export') }}" class="flex items-center gap-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 px-4 py-2 rounded-lg font-medium text-sm transition-colors text-textPrimary dark:text-darkText">
                <i class="ph-bold ph-download-simple"></i> Export CSV
            </a>
            <a href="{{ route('reports.print') }}" target="_blank" class="flex items-center gap-2 bg-accentRed text-white hover:bg-red-600 px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                <i class="ph-bold ph-printer"></i> Cetak PDF Resmi
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="text-textSecondary border-b border-gray-100 dark:border-gray-800">
                <tr>
                    <th class="pb-3 font-medium text-left text-textSecondary text-sm">No. Transaksi</th>
                    <th class="pb-3 font-medium text-left text-textSecondary text-sm">Waktu</th>
                    <th class="pb-3 font-medium text-left text-textSecondary text-sm">Kategori</th>
                    <th class="pb-3 font-medium text-right text-textSecondary text-sm">Nominal</th>
                    <th class="pb-3 font-medium text-center text-textSecondary text-sm">Status</th>
                    <th class="pb-3 font-medium text-center text-textSecondary text-sm">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                @forelse($transactions as $trx)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <td class="py-4 font-mono text-brandBlue">{{ $trx->transaction_number }}</td>
                    <td class="py-4 text-textSecondary">{{ \Carbon\Carbon::parse($trx->created_at)->format('d M Y, H:i') }}</td>
                    <td class="py-4 font-medium dark:text-gray-300">{{ $trx->category }}</td>
                    <td class="py-4 text-right font-mono {{ $trx->type == 'IN' ? 'text-accentGreen' : 'text-textPrimary dark:text-white' }}">
                        {{ $trx->type == 'IN' ? '+' : '-' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
                    </td>
                    <td class="py-4 text-center">
                        @if($trx->status == 'Success')
                            <span class="px-3 py-1 bg-green-50 text-accentGreen rounded-full text-xs font-semibold dark:bg-green-900/30">Berhasil</span>
                        @elseif($trx->status == 'Anomaly')
                            <span class="px-3 py-1 bg-red-50 text-accentRed rounded-full text-xs font-semibold dark:bg-red-900/30">Anomali</span>
                        @else
                            <span class="px-3 py-1 bg-yellow-50 text-yellow-600 rounded-full text-xs font-semibold dark:bg-yellow-900/30">{{ $trx->status }}</span>
                        @endif
                    </td>
                    <td class="py-4 text-center">
                        <a href="{{ route('reports.detail', $trx->id) }}" class="text-brandBlue hover:text-blue-700 font-semibold text-xs flex items-center justify-center gap-1">
                            Detail <i class="ph-bold ph-arrow-right"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-textSecondary">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <i class="ph-fill ph-folder-open text-4xl text-gray-300 dark:text-gray-600"></i>
                            <p>Tidak ada data laporan yang ditemukan.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>
</main>
@endsection
@push('scripts')
<script>
    gsap.from(".gsap-fade", { opacity: 0, y: 20, duration: 0.8, stagger: 0.2, ease: "power2.out" });

    // Real-time Metrics Polling
    const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num);
    const formatMoneyM = (num) => new Intl.NumberFormat('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 }).format(num / 1e9);

    setInterval(async () => {
        try {
            const res = await axios.get('/api/reports-metrics');
            const data = res.data;
            
            const docEl = document.getElementById('metric-total-doc');
            const inEl = document.getElementById('metric-total-in');
            const outEl = document.getElementById('metric-total-out');

            if (docEl.innerText !== formatNumber(data.total_doc)) {
                docEl.innerText = formatNumber(data.total_doc);
                gsap.from(docEl, { color: '#34C759', scale: 1.1, duration: 0.5 });
            }
            if (inEl.innerText !== formatMoneyM(data.total_in)) {
                inEl.innerText = formatMoneyM(data.total_in);
                gsap.from(inEl, { color: '#34C759', scale: 1.1, duration: 0.5 });
            }
            if (outEl.innerText !== formatMoneyM(data.total_out)) {
                outEl.innerText = formatMoneyM(data.total_out);
                gsap.from(outEl, { color: '#FF3B30', scale: 1.1, duration: 0.5 });
            }
        } catch (e) {
            console.error('Failed to fetch live metrics', e);
        }
    }, 1500);
</script>
@endpush
