@extends('layouts.app')
@section('title', '403 - Akses Ditolak')

@section('content')
<main class="max-w-3xl mx-auto mt-20 bento-card p-12 text-center gsap-fade">
    <div class="w-24 h-24 bg-red-50 dark:bg-red-900/20 text-accentRed rounded-3xl mx-auto flex items-center justify-center mb-6">
        <i class="ph-fill ph-shield-warning text-5xl"></i>
    </div>
    <h1 class="text-4xl font-bold tracking-tight text-textPrimary dark:text-darkText mb-3">403 - Protokol Keamanan Aktif</h1>
    <p class="text-textSecondary text-lg mb-8">Maaf, kredensial (Role) Anda tidak memiliki otorisasi (<i>Clearance Level</i>) yang cukup untuk mengakses dokumen rahasia negara ini.</p>
    
    <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700 text-sm font-mono text-left mb-8 max-w-md mx-auto">
        <div><span class="text-gray-400">User:</span> {{ Auth::user()->email ?? 'GUEST' }}</div>
        <div><span class="text-gray-400">Role:</span> {{ Auth::user()->role ?? 'UNKNOWN' }}</div>
        <div><span class="text-gray-400">Action:</span> FORBIDDEN_ACCESS</div>
        <div class="text-accentRed mt-2 animate-pulse">>> Insiden telah dicatat di Audit Trail</div>
    </div>

    <a href="/" class="inline-flex items-center gap-2 bg-brandBlue hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
        <i class="ph-bold ph-arrow-left"></i> Kembali ke Pusat Komando
    </a>
</main>
@endsection
@push('scripts')
<script>gsap.from(".gsap-fade", { opacity: 0, y: 20, duration: 0.5, ease: "power2.out" });</script>
@endpush
