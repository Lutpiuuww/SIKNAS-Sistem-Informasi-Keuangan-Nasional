@extends('layouts.app')
@section('title', 'Persetujuan Transaksi (Pending)')

@section('content')
<header class="max-w-7xl mx-auto mb-12 mt-8 gsap-fade">
    <h1 class="text-4xl font-bold tracking-tight mb-3">Otorisasi Transaksi</h1>
    <p class="text-textSecondary text-lg">Modul Workflow Berjenjang: Review dan Setujui draf transaksi dari staf (Maker-Checker-Approver).</p>
</header>
<main class="max-w-7xl mx-auto bento-card p-8 gsap-fade">
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 text-accentGreen rounded-xl font-medium text-sm">{{ session('success') }}</div>
    @endif
    <h3 class="font-bold text-lg mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">Daftar Transaksi Menunggu Persetujuan (Pending)</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="text-textSecondary border-b border-gray-100 dark:border-gray-800">
                <tr>
                    <th class="pb-3 font-medium">No. Transaksi</th>
                    <th class="pb-3 font-medium">Kategori</th>
                    <th class="pb-3 font-medium">Wilayah</th>
                    <th class="pb-3 font-medium text-right">Nominal</th>
                    <th class="pb-3 font-medium text-center">Status</th>
                    <th class="pb-3 font-medium text-center">Aksi Otorisasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                @forelse($pending as $p)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                    <td class="py-4 font-mono text-xs">{{ $p->transaction_number }}</td>
                    <td class="py-4 font-semibold text-textPrimary dark:text-darkText">{{ $p->category }}</td>
                    <td class="py-4 text-textSecondary">{{ $p->region_name ?? 'Nasional' }}</td>
                    <td class="py-4 text-right font-mono font-bold text-textPrimary dark:text-darkText">Rp {{ number_format($p->amount / 1000000000, 2, ',', '.') }} M</td>
                    <td class="py-4 text-center">
                        <span class="px-3 py-1 bg-yellow-50 text-yellow-600 rounded-full text-xs font-semibold border border-yellow-200">Pending</span>
                    </td>
                    <td class="py-4 flex justify-center gap-2">
                        <form action="{{ route('approvals.action', $p->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="bg-accentGreen text-white px-3 py-1.5 rounded text-xs font-semibold hover:bg-green-600">Setujui</button>
                        </form>
                        <form action="{{ route('approvals.action', $p->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="bg-accentRed text-white px-3 py-1.5 rounded text-xs font-semibold hover:bg-red-600">Tolak</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-8 text-center text-textSecondary italic">Tidak ada transaksi yang menunggu persetujuan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</main>
@endsection
@push('scripts')
<script>gsap.from(".gsap-fade", { opacity: 0, y: 20, duration: 0.6, stagger: 0.1, ease: "power2.out" });</script>
@endpush
