@extends('layouts.app')
@section('title', 'API Gateway & Interoperabilitas')

@section('content')
<header class="max-w-7xl mx-auto mb-12 mt-8 gsap-fade">
    <h1 class="text-4xl font-bold tracking-tight mb-3">Interoperabilitas & API Gateway</h1>
    <p class="text-textSecondary text-lg">Kelola integrasi dengan Sistem Pemerintahan Berbasis Elektronik (SPBE) Kementerian lain via Webhooks & API Keys.</p>
</header>
<main class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6 gsap-fade">
    <div class="md:col-span-2 space-y-6">
        <div class="bento-card p-8 border-t-4 border-t-purple-500">
            <div class="flex justify-between items-center mb-2">
                <h3 class="font-bold text-lg flex items-center gap-2"><i class="ph-bold ph-key text-purple-500"></i> Brankas API Keys</h3>
                <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded font-bold flex items-center gap-1"><i class="ph-fill ph-warning-circle"></i> HIGHLY CONFIDENTIAL</span>
            </div>
            <p class="text-sm text-textSecondary mb-6">Demi keamanan nasional, API Key penuh hanya ditampilkan 1 kali saat pembuatan (Terkunci oleh enkripsi bcrypt di database).</p>
            
            <div class="space-y-4">
                @if(session('new_token'))
                <div class="bg-green-50 p-4 border border-green-200 rounded-xl mb-4">
                    <h4 class="font-bold text-green-800 mb-2">API Key Baru Dibuat!</h4>
                    <p class="text-sm text-green-700 mb-2">Simpan token ini sekarang. Token tidak akan ditampilkan lagi.</p>
                    <input type="text" readonly value="{{ session('new_token') }}" class="w-full bg-white border border-green-300 rounded px-3 py-2 font-mono text-sm text-gray-800" onclick="this.select()">
                </div>
                @endif

                @forelse($tokens as $token)
                <div class="p-4 bg-gray-50 dark:bg-darkBg border border-gray-200 dark:border-gray-700 rounded-xl relative overflow-hidden">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <div class="font-bold text-textPrimary dark:text-darkText">{{ $token->name }}</div>
                            <div class="text-xs text-textSecondary">Dibuat: {{ $token->created_at->diffForHumans() }} • Terakhir digunakan: {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Belum pernah' }}</div>
                        </div>
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold">Aktif</span>
                    </div>
                </div>
                @empty
                <div class="text-center text-textSecondary text-sm py-4">Belum ada API Key yang dibuat.</div>
                @endforelse
            </div>
            
            <form action="{{ route('api.gateway.generate') }}" method="POST" class="mt-6 flex gap-2">
                @csrf
                <input type="text" name="name" placeholder="Nama Aplikasi / Integrasi (contoh: Kemenkeu SAKTI)" required class="flex-1 px-4 py-2 bg-gray-50 dark:bg-darkBg border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 text-textPrimary dark:text-darkText">
                <button type="submit" class="border-2 border-dashed border-gray-300 dark:border-gray-700 text-textSecondary hover:text-brandBlue hover:border-brandBlue px-6 py-2 rounded-xl font-semibold transition-colors flex items-center justify-center gap-2">
                    <i class="ph-bold ph-plus"></i> Generate API Key Baru
                </button>
            </form>
        </div>

        <div class="bento-card p-8 bg-blue-50 dark:bg-blue-900/10 border-blue-100 dark:border-blue-900/30">
            <h3 class="font-bold text-lg text-brandBlue mb-2 flex items-center gap-2"><i class="ph-bold ph-lightning"></i> Infrastruktur WebSockets & Kafka</h3>
            <p class="text-sm text-textSecondary mb-4">Sistem SIKNAS menggunakan <b>Apache Kafka</b> untuk event-streaming transaksi kecepatan tinggi, dan <b>WebSockets (Laravel Reverb)</b> untuk mem-push notifikasi dasbor tanpa <i>polling</i> database.</p>
            <div class="bg-gray-900 p-4 rounded-lg font-mono text-xs text-green-400 overflow-x-auto">
                <div>[Kafka] Topic: siknas.transactions.live - STATUS: CONNECTED</div>
                <div>[Redis] Cache Layer - STATUS: HIT RATIO 94%</div>
                <div>[WSS] wss://api.siknas.gov/stream - CLIENTS: 14,204</div>
            </div>
        </div>
    </div>

    <div class="bento-card p-6 h-max">
        <h3 class="font-bold text-lg mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">Dokumentasi API</h3>
        
        <div class="mb-4">
            <div class="text-xs font-bold text-gray-500 mb-1">ENDPOINT</div>
            <code class="text-sm bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded break-all">POST https://api.siknas.gov/v1/transactions</code>
        </div>
        
        <div class="mb-4">
            <div class="text-xs font-bold text-gray-500 mb-1">HEADERS</div>
            <pre class="text-xs bg-gray-900 text-gray-300 p-3 rounded">
Authorization: Bearer {API_KEY}
Content-Type: application/json</pre>
        </div>

        <div class="mb-4">
            <div class="text-xs font-bold text-gray-500 mb-1">PAYLOAD (Contoh)</div>
            <pre class="text-xs bg-gray-900 text-green-400 p-3 rounded overflow-x-auto">
{
  "category": "TKDD",
  "amount": 2500000000,
  "type": "OUT",
  "region_id": 32,
  "description": "BOS Tahap 2"
}</pre>
        </div>
        <a href="#" class="text-brandBlue text-sm font-semibold hover:underline">Lihat Dokumentasi Lengkap (Swagger) -></a>
    </div>
</main>
@endsection
@push('scripts')
<script>gsap.from(".gsap-fade", { opacity: 0, y: 20, duration: 0.6, stagger: 0.1, ease: "power2.out" });</script>
@endpush
