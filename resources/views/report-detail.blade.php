@extends('layouts.app')
@section('title', 'Detail Transaksi ' . $trx->transaction_number)

@section('content')
<div class="max-w-4xl mx-auto mt-8 mb-4 gsap-fade">
    <a href="{{ route('reports.index') }}" class="text-textSecondary hover:text-brandBlue font-medium text-sm flex items-center gap-1 w-max mb-6 transition-colors">
        <i class="ph-bold ph-arrow-left"></i> Kembali ke Daftar Laporan
    </a>
</div>

<main class="max-w-4xl mx-auto bento-card p-8 gsap-fade relative overflow-hidden">
    @if($trx->status == 'Anomaly')
        <div class="absolute top-0 left-0 w-full h-2 bg-accentRed"></div>
    @else
        <div class="absolute top-0 left-0 w-full h-2 bg-brandBlue"></div>
    @endif

    <div class="flex justify-between items-start border-b border-gray-100 dark:border-gray-800 pb-6 mb-8">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-textPrimary dark:text-darkText">{{ $trx->transaction_number }}</h1>
            <p class="text-textSecondary mt-1">Direkam pada: {{ \Carbon\Carbon::parse($trx->created_at)->translatedFormat('l, d F Y H:i:s') }} UTC</p>
        </div>
        <div class="text-right">
            @if($trx->status == 'Success')
                <span class="px-4 py-1.5 bg-green-50 text-accentGreen rounded-full text-sm font-semibold dark:bg-green-900/30 border border-green-200 dark:border-green-800">SAH / SUCCESS</span>
            @elseif($trx->status == 'Anomaly')
                <span class="px-4 py-1.5 bg-red-50 text-accentRed rounded-full text-sm font-semibold dark:bg-red-900/30 border border-red-200 dark:border-red-800 flex items-center gap-1"><i class="ph-fill ph-warning"></i> ANOMALI TERDETEKSI</span>
            @else
                <span class="px-4 py-1.5 bg-gray-100 text-gray-600 rounded-full text-sm font-semibold dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">{{ $trx->status }}</span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
        <div class="bg-bgLight dark:bg-darkBg p-6 rounded-2xl">
            <h3 class="text-sm font-semibold text-textSecondary uppercase tracking-wider mb-4">Informasi Dasar</h3>
            
            <div class="mb-4">
                <div class="text-xs text-textSecondary mb-1">Kategori Induk</div>
                <div class="font-medium text-lg">{{ $trx->category }}</div>
            </div>
            
            <div class="mb-4">
                <div class="text-xs text-textSecondary mb-1">Total Nominal</div>
                <div class="font-mono text-2xl font-bold {{ $trx->type == 'IN' ? 'text-accentGreen' : 'text-brandBlue' }}">
                    {{ $trx->type == 'IN' ? '+' : '-' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
                </div>
            </div>

            <div>
                <div class="text-xs text-textSecondary mb-1">Jenis Mutasi</div>
                <div class="font-medium flex items-center gap-1">
                    @if($trx->type == 'IN')
                        <span class="w-2 h-2 rounded-full bg-accentGreen"></span> Uang Masuk (Pendapatan/Penerimaan)
                    @else
                        <span class="w-2 h-2 rounded-full bg-brandBlue"></span> Uang Keluar (Belanja/Pengeluaran)
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-bgLight dark:bg-darkBg p-6 rounded-2xl border {{ $trx->status == 'Anomaly' ? 'border-red-200 dark:border-red-900' : 'border-transparent' }}">
            <h3 class="text-sm font-semibold text-textSecondary uppercase tracking-wider mb-4">Konteks & Tujuan</h3>
            
            <div class="mb-4">
                <div class="text-xs text-textSecondary mb-1">Deskripsi Sistem</div>
                <div class="font-medium text-textPrimary">{{ $trx->description }}</div>
            </div>
            
            <div>
                <div class="text-xs text-textSecondary mb-1">Lokasi Alokasi / Sumber Wilayah</div>
                @if($trx->region_id)
                    <div class="font-medium flex items-center gap-2">
                        <i class="ph-fill ph-map-pin text-brandBlue"></i> {{ $trx->region_name }} 
                        <span class="text-xs text-gray-500 bg-gray-200 dark:bg-gray-700 px-2 py-0.5 rounded">{{ $trx->region_type }}</span>
                    </div>
                @else
                    <div class="font-medium flex items-center gap-2">
                        <i class="ph-fill ph-buildings text-gray-400"></i> Terpusat (Nasional)
                    </div>
                @endif
            </div>

            @if($trx->status == 'Anomaly')
            <div class="mt-6 bg-red-50 dark:bg-red-900/20 p-3 rounded-lg border border-red-100 dark:border-red-800/50">
                <div class="text-xs font-bold text-accentRed flex items-center gap-1 mb-1"><i class="ph-bold ph-shield-warning"></i> CATATAN AUDIT (AI)</div>
                <div class="text-sm text-red-800 dark:text-red-300">Transaksi ini diblokir atau ditandai anomali oleh Sentinel karena nominal pengeluaran melebihi batas wajar harian kementerian/lembaga terkait. Direkomendasikan untuk investigasi manual.</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Dynamic Breakdown -->
    <div>
        <h3 class="text-xl font-bold mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">Rincian Komponen Transaksi</h3>
        <p class="text-sm text-textSecondary mb-6">Berikut adalah detail dari mana uang tersebut berasal (Pajak) atau untuk apa uang tersebut digunakan (Program Nasional/Daerah).</p>
        
        <div class="space-y-4">
            @foreach($breakdown as $item)
            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-700 hover:border-blue-200 dark:hover:border-blue-900 transition-colors">
                <div class="flex items-center gap-3 mb-2 sm:mb-0">
                    <div class="w-10 h-10 rounded-lg bg-white dark:bg-gray-700 shadow-sm flex items-center justify-center text-brandBlue">
                        <i class="ph-bold ph-chart-pie-slice"></i>
                    </div>
                    <div class="font-medium">{{ $item['name'] }}</div>
                </div>
                <div class="text-right">
                    <div class="font-mono font-bold text-lg text-textPrimary dark:text-darkText">Rp {{ number_format($item['amount'], 0, ',', '.') }}</div>
                    <div class="text-xs text-textSecondary">{{ round(($item['amount'] / $trx->amount) * 100) }}% dari Total</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</main>
@endsection
@push('scripts')
<script>gsap.from(".gsap-fade", { opacity: 0, y: 20, duration: 0.6, stagger: 0.1, ease: "power2.out" });</script>
@endpush
