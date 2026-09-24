@extends('layouts.app')
@section('title', 'Login Auditor')

@section('content')
<main class="max-w-md mx-auto mt-20 gsap-fade">
    <div class="bento-card p-8">
        <div class="w-12 h-12 bg-brandBlue rounded-xl flex items-center justify-center text-white mb-6 mx-auto">
            <i class="ph-bold ph-shield-check text-2xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-center mb-2">Portal Auditor</h2>
        <p class="text-textSecondary text-center text-sm mb-8">Gunakan kredensial SIKNAS Anda untuk masuk.</p>
        
        <form class="flex flex-col gap-4" method="POST" action="{{ route('login') }}">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-textSecondary mb-1 uppercase tracking-wider">NIP / Email Instansi</label>
                <input type="email" name="email" value="{{ old('email', 'auditor@siknas.gov') }}" class="w-full px-4 py-3 bg-bgLight dark:bg-gray-800 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brandBlue transition-shadow border border-transparent focus:border-brandBlue/30" placeholder="Masukkan Email Anda">
                @error('email') <span class="text-xs text-accentRed mt-1">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-textSecondary mb-1 uppercase tracking-wider">Password Kriptografik</label>
                <input type="password" name="password" class="w-full px-4 py-3 bg-bgLight dark:bg-gray-800 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brandBlue transition-shadow border border-transparent focus:border-brandBlue/30" placeholder="••••••••">
            </div>
            <div class="flex items-center justify-between mt-2">
                <label class="flex items-center gap-2 text-sm text-textSecondary cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded text-brandBlue focus:ring-brandBlue border-gray-300"> Ingat Saya
                </label>
                <a href="#" class="text-sm font-medium text-brandBlue hover:underline">Lupa Sandi?</a>
            </div>
            <button type="submit" class="w-full bg-textPrimary text-white font-medium py-3 rounded-xl mt-4 hover:bg-black transition-colors shadow-floating relative overflow-hidden group">
                <span class="relative z-10">Otentikasi Masuk</span>
                <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform"></div>
            </button>
        </form>
        <div class="mt-6 text-center text-xs text-textSecondary bg-gray-50 p-3 rounded-lg flex items-center gap-2 justify-center border border-gray-100">
            <i class="ph-fill ph-lock-key text-accentGreen"></i> Terenkripsi dengan Standar AES-256
        </div>
    </div>
</main>
@endsection
@push('scripts')
<script>gsap.from(".gsap-fade", { opacity: 0, y: 20, duration: 0.6, ease: "power2.out" });</script>
@endpush
