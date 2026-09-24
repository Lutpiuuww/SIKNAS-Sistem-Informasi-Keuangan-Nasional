@extends('layouts.app')
@section('title', 'Audit Trail Keamanan')

@section('content')
<header class="max-w-7xl mx-auto mb-12 mt-8 gsap-fade">
    <h1 class="text-4xl font-bold tracking-tight mb-3 text-accentRed">Log Aktivitas Keamanan (Audit Trail)</h1>
    <p class="text-textSecondary text-lg">Catatan kekal (*immutable log*) seluruh pergerakan pengguna dan akses sistem.</p>
</header>
<main class="max-w-7xl mx-auto bento-card p-8 gsap-fade relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: repeating-linear-gradient(45deg, #FF3B30 0, #FF3B30 2px, transparent 2px, transparent 8px);"></div>
    <div class="relative z-10 overflow-x-auto">
        <table class="w-full text-left text-sm font-mono">
            <thead class="text-textSecondary border-b border-red-100 bg-red-50/50">
                <tr>
                    <th class="py-3 px-4 font-semibold uppercase tracking-wider text-xs">Timestamp (UTC)</th>
                    <th class="py-3 px-4 font-semibold uppercase tracking-wider text-xs">Aktor (Role)</th>
                    <th class="py-3 px-4 font-semibold uppercase tracking-wider text-xs">Aktivitas</th>
                    <th class="py-3 px-4 font-semibold uppercase tracking-wider text-xs">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($logs as $log)
                <tr class="hover:bg-red-50/30 transition-colors">
                    <td class="py-3 px-4 text-textSecondary">{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s.v') }}</td>
                    <td class="py-3 px-4 font-medium">{{ $log->name }} <span class="ml-2 text-xs bg-gray-100 px-2 py-0.5 rounded text-gray-500">{{ strtoupper($log->role) }}</span></td>
                    <td class="py-3 px-4 text-textPrimary">{{ $log->action }}</td>
                    <td class="py-3 px-4 text-accentRed opacity-80">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                </tr>
                @endforeach
                @if($logs->isEmpty())
                <tr>
                    <td colspan="4" class="py-8 text-center text-textSecondary italic">Belum ada catatan aktivitas keamanan.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</main>
@endsection
@push('scripts')
<script>gsap.from(".gsap-fade", { opacity: 0, y: 20, duration: 0.8, stagger: 0.2, ease: "power2.out" });</script>
@endpush
