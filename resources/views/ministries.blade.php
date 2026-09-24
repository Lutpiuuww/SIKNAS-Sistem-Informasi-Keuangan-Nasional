@extends('layouts.app')
@section('title', 'Master Data Kementerian')

@section('content')
<header class="max-w-7xl mx-auto mb-12 mt-8 gsap-fade">
    <h1 class="text-4xl font-bold tracking-tight mb-3">Data Kementerian & Lembaga</h1>
    <p class="text-textSecondary text-lg">Kelola pagu anggaran pusat untuk masing-masing instansi negara.</p>
</header>
<main class="max-w-7xl mx-auto bento-card p-8 gsap-fade">
    <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-4 mb-6">
        <h3 class="font-bold text-lg">Daftar Kementerian & Lembaga Negara (Master Data Terkunci)</h3>
        <span class="text-xs font-semibold text-accentRed bg-red-50 dark:bg-red-900/30 px-3 py-1 rounded-full border border-red-200 dark:border-red-800 flex items-center gap-1"><i class="ph-fill ph-lock-key"></i> Sistem Immutable</span>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="text-textSecondary border-b border-gray-100 dark:border-gray-800">
                <tr>
                    <th class="pb-3 font-medium text-center w-16">No</th>
                    <th class="pb-3 font-medium w-48">Kode Instansi</th>
                    <th class="pb-3 font-medium">Nama Kementerian/Lembaga</th>
                    <th class="pb-3 font-medium text-right">Alokasi Anggaran (Pagu)</th>
                    <th class="pb-3 font-medium text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                @foreach($ministries as $index => $m)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="py-4 text-center text-textSecondary">{{ $index + 1 }}</td>
                    <td class="py-4 text-xs font-bold text-brandBlue uppercase">{{ $m->code }}</td>
                    <td class="py-4 font-semibold text-textPrimary dark:text-darkText">{{ $m->name }}</td>
                    <td class="py-4 text-right font-mono font-bold text-textPrimary dark:text-darkText">
                        Rp {{ number_format($m->budget_allocated / 1000000000000, 1, ',', '.') }} Triliun
                    </td>
                    <td class="py-4 text-center">
                        <a href="{{ route('ministries.detail', $m->id) }}" class="text-brandBlue hover:text-blue-700 font-semibold text-xs flex items-center justify-center gap-1">
                            Rincian <i class="ph-bold ph-arrow-right"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</main>
@endsection

@push('scripts')
<script>gsap.from(".gsap-fade", { opacity: 0, y: 20, duration: 0.6, stagger: 0.1, ease: "power2.out" });</script>
@endpush
