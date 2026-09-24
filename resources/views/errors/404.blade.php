@extends('layouts.app')
@section('title', '404 - Halaman Tidak Ditemukan')

@section('content')
<main class="max-w-3xl mx-auto mt-20 bento-card p-12 text-center gsap-fade">
    <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 text-textSecondary rounded-3xl mx-auto flex items-center justify-center mb-6">
        <i class="ph-fill ph-magnifying-glass text-5xl"></i>
    </div>
    <h1 class="text-4xl font-bold tracking-tight text-textPrimary dark:text-darkText mb-3">404 - Data Tidak Ditemukan</h1>
    <p class="text-textSecondary text-lg mb-8">Halaman atau arsip transaksi yang Anda cari tidak ada di dalam database pusat SIKNAS.</p>
    
    <a href="/" class="inline-flex items-center gap-2 bg-brandBlue hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
        <i class="ph-bold ph-arrow-left"></i> Kembali ke Pusat Komando
    </a>
</main>
@endsection
@push('scripts')
<script>gsap.from(".gsap-fade", { opacity: 0, y: 20, duration: 0.5, ease: "power2.out" });</script>
@endpush
