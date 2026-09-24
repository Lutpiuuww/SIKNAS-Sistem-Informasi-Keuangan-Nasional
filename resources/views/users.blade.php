@extends('layouts.app')
@section('title', 'Manajemen Pengguna')

@section('content')
<header class="max-w-7xl mx-auto mb-12 mt-8 gsap-fade">
    <h1 class="text-4xl font-bold tracking-tight mb-3">Manajemen Pengguna</h1>
    <p class="text-textSecondary text-lg">Kelola akses akun Menteri, Auditor, dan Gubernur daerah.</p>
</header>
<main class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6 gsap-fade">
    <div class="md:col-span-2 bento-card p-8">
        <h3 class="font-bold text-lg mb-4">Daftar Akun Terdaftar</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-textSecondary border-b border-gray-100 dark:border-gray-800">
                    <tr>
                        <th class="pb-3 font-medium">Nama Lengkap</th>
                        <th class="pb-3 font-medium">Email</th>
                        <th class="pb-3 font-medium text-center">Peran (Role)</th>
                        <th class="pb-3 font-medium">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @foreach($users as $u)
                    <tr>
                        <td class="py-4 font-semibold text-textPrimary dark:text-darkText">{{ $u->name }}</td>
                        <td class="py-4 text-textSecondary">{{ $u->email }}</td>
                        <td class="py-4 text-center">
                            @if($u->role === 'super_admin')
                                <span class="px-3 py-1 bg-blue-50 text-brandBlue rounded-lg text-xs font-semibold dark:bg-blue-900/30">Menteri / Pusat</span>
                            @elseif($u->role === 'auditor')
                                <span class="px-3 py-1 bg-orange-50 text-orange-600 rounded-lg text-xs font-semibold dark:bg-orange-900/30">Auditor Utama</span>
                            @else
                                <span class="px-3 py-1 bg-green-50 text-accentGreen rounded-lg text-xs font-semibold dark:bg-green-900/30">Gubernur Pemda</span>
                            @endif
                        </td>
                        <td class="py-4 text-textSecondary text-xs">
                            {{ $u->region_id ? 'Mengelola ' . $u->region_name : 'Akses Nasional' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="bento-card p-6 h-max">
        <h3 class="font-bold text-lg mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">Tambah Akun Baru</h3>
        @if(session('success'))
            <div class="text-accentGreen text-sm font-semibold mb-4 bg-green-50 dark:bg-green-900/30 p-2 rounded-lg">{{ session('success') }}</div>
        @endif
        <form action="/users" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-textPrimary dark:text-darkText mb-1">Nama Lengkap</label>
                <input type="text" name="name" required class="w-full bg-gray-50 dark:bg-darkBg border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm text-textPrimary dark:text-darkText focus:outline-none focus:border-brandBlue">
            </div>
            <div>
                <label class="block text-sm font-semibold text-textPrimary dark:text-darkText mb-1">Email SIKNAS</label>
                <input type="email" name="email" required placeholder="nama@siknas.gov" class="w-full bg-gray-50 dark:bg-darkBg border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm text-textPrimary dark:text-darkText focus:outline-none focus:border-brandBlue">
            </div>
            <div>
                <label class="block text-sm font-semibold text-textPrimary dark:text-darkText mb-1">Password</label>
                <input type="password" name="password" required class="w-full bg-gray-50 dark:bg-darkBg border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm text-textPrimary dark:text-darkText focus:outline-none focus:border-brandBlue">
            </div>
            <div>
                <label class="block text-sm font-semibold text-textPrimary dark:text-darkText mb-1">Peran Akses (Role)</label>
                <select name="role" required id="roleSelect" onchange="toggleRegion()" class="w-full bg-gray-50 dark:bg-darkBg border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm text-textPrimary dark:text-darkText focus:outline-none focus:border-brandBlue">
                    <option value="auditor">Auditor (Pusat)</option>
                    <option value="super_admin">Super Admin (Menteri)</option>
                    <option value="gubernur">Gubernur (Pemerintah Daerah)</option>
                </select>
            </div>
            <div id="regionWrap" style="display: none;">
                <label class="block text-sm font-semibold text-textPrimary dark:text-darkText mb-1">Pilih Provinsi</label>
                <select name="region_id" class="w-full bg-gray-50 dark:bg-darkBg border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm text-textPrimary dark:text-darkText focus:outline-none focus:border-brandBlue">
                    @foreach($regions as $r)
                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="w-full bg-brandBlue text-white font-semibold py-2 rounded-lg text-sm hover:bg-blue-700 transition-colors">Buat Akun</button>
        </form>
    </div>
</main>
@endsection

@push('scripts')
<script>
    gsap.from(".gsap-fade", { opacity: 0, y: 20, duration: 0.6, stagger: 0.1, ease: "power2.out" });
    window.toggleRegion = () => {
        const role = document.getElementById('roleSelect').value;
        document.getElementById('regionWrap').style.display = role === 'gubernur' ? 'block' : 'none';
    };
</script>
@endpush
