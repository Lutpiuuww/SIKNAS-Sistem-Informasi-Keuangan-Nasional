@extends('layouts.app')
@section('title', 'Detail Pagu: ' . $ministry->name)

@section('content')
<div class="max-w-4xl mx-auto mt-8 mb-4 gsap-fade">
    <a href="/ministries" class="text-textSecondary hover:text-brandBlue font-medium text-sm flex items-center gap-1 w-max mb-6 transition-colors">
        <i class="ph-bold ph-arrow-left"></i> Kembali ke Daftar Kementerian
    </a>
</div>

<main class="max-w-4xl mx-auto bento-card p-8 gsap-fade relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-2 bg-brandBlue"></div>

    <div class="flex justify-between items-start border-b border-gray-100 dark:border-gray-800 pb-6 mb-8">
        <div>
            <div class="text-xs font-bold text-brandBlue uppercase tracking-wider mb-2">{{ $ministry->code }}</div>
            <h1 class="text-3xl font-bold tracking-tight text-textPrimary dark:text-darkText">{{ $ministry->name }}</h1>
            <p class="text-textSecondary mt-1">Rincian Postur Anggaran & Rencana Kerja Pemerintah (RKP) Nasional</p>
        </div>
        <div class="text-right">
            <div class="text-xs text-textSecondary mb-1">Total Pagu Alokasi</div>
            <div class="font-mono text-2xl font-bold text-textPrimary dark:text-darkText">
                Rp {{ number_format($ministry->budget_allocated / 1000000000000, 1, ',', '.') }} Triliun
            </div>
        </div>
    </div>

    <div>
        <h3 class="text-xl font-bold mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">Rencana Program Prioritas</h3>
        <p class="text-sm text-textSecondary mb-6">Berikut adalah postur alokasi APBN untuk instansi ini yang didasarkan pada proporsi riil fungsi kementerian (Data Referensi RKP/APBN Aktual).</p>
        
        <div class="space-y-4">
            @foreach($programs as $prog)
            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-700 hover:border-blue-200 dark:hover:border-blue-900 transition-colors">
                <div class="flex items-start gap-4 mb-3 sm:mb-0 w-full sm:w-2/3">
                    <div class="w-10 h-10 shrink-0 rounded-lg bg-white dark:bg-gray-700 shadow-sm flex items-center justify-center text-brandBlue mt-1">
                        <i class="ph-bold ph-target"></i>
                    </div>
                    <div>
                        <div class="font-bold text-textPrimary dark:text-darkText text-base mb-1">{{ $prog['name'] }}</div>
                        <div class="text-sm text-textSecondary leading-relaxed">{{ $prog['desc'] }}</div>
                    </div>
                </div>
                <div class="text-left sm:text-right w-full sm:w-1/3">
                    <div class="font-mono font-bold text-lg text-textPrimary dark:text-darkText">
                        Rp {{ number_format($prog['amount'] / 1000000000000, 2, ',', '.') }} T
                    </div>
                    <div class="text-xs text-brandBlue font-semibold mt-1">
                        {{ number_format(($prog['amount'] / $ministry->budget_allocated) * 100, 1, ',', '.') }}% Pagu
                    </div>
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
