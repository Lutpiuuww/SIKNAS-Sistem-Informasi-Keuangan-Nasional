@extends('layouts.app')
@section('title', 'Entri Transaksi Baru')

@section('content')
<div class="max-w-3xl mx-auto mt-8 mb-4 gsap-fade">
    <a href="{{ route('reports.index') }}" class="text-textSecondary hover:text-brandBlue font-medium text-sm flex items-center gap-1 w-max mb-6 transition-colors">
        <i class="ph-bold ph-arrow-left"></i> Kembali ke Laporan
    </a>
    <h1 class="text-3xl font-bold tracking-tight text-textPrimary dark:text-darkText mb-2">Entri Transaksi Manual</h1>
    <p class="text-textSecondary text-sm">Formulir resmi pencatatan mutasi kas negara ke dalam sistem SIKNAS.</p>
</div>

<main class="max-w-3xl mx-auto bento-card p-8 gsap-fade">
    <form action="{{ route('reports.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-textPrimary dark:text-darkText mb-2">Tipe Transaksi</label>
                <select name="type" id="typeSelect" required onchange="updateCategories()" class="w-full bg-gray-50 dark:bg-darkBg border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-2.5 text-textPrimary dark:text-darkText focus:ring-2 focus:ring-brandBlue outline-none transition-colors">
                    <option value="">-- Pilih Tipe --</option>
                    <option value="IN">Pemasukan (IN)</option>
                    <option value="OUT">Pengeluaran (OUT)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-textPrimary dark:text-darkText mb-2">Kategori</label>
                <select name="category" id="categorySelect" required class="w-full bg-gray-50 dark:bg-darkBg border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-2.5 text-textPrimary dark:text-darkText focus:ring-2 focus:ring-brandBlue outline-none transition-colors disabled:opacity-50" disabled>
                    <option value="">-- Pilih Tipe Terlebih Dahulu --</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-textPrimary dark:text-darkText mb-2">Nominal (Rupiah)</label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">Rp</span>
                <input type="number" name="amount" required min="0" step="1000" placeholder="Contoh: 5000000000" class="w-full pl-12 pr-4 py-2.5 bg-gray-50 dark:bg-darkBg border border-gray-200 dark:border-gray-700 rounded-lg text-textPrimary dark:text-darkText focus:ring-2 focus:ring-brandBlue outline-none transition-colors font-mono">
            </div>
            <p class="text-xs text-textSecondary mt-1">* Masukkan angka penuh tanpa titik (Misal 5 Miliar = 5000000000)</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-textPrimary dark:text-darkText mb-2">Wilayah Terkait (Opsional)</label>
            <select name="region_id" class="w-full bg-gray-50 dark:bg-darkBg border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-2.5 text-textPrimary dark:text-darkText focus:ring-2 focus:ring-brandBlue outline-none transition-colors">
                <option value="">-- Nasional / Terpusat --</option>
                @foreach($regions as $r)
                <option value="{{ $r->id }}">{{ $r->name }}</option>
                @endforeach
            </select>
            <p class="text-xs text-textSecondary mt-1">* Kosongkan jika ini adalah Belanja Pusat atau Pendapatan Nasional</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-textPrimary dark:text-darkText mb-2">Deskripsi / Konteks Kegiatan</label>
            <textarea name="description" required rows="3" placeholder="Jelaskan peruntukan atau sumber transaksi ini..." class="w-full bg-gray-50 dark:bg-darkBg border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-3 text-textPrimary dark:text-darkText focus:ring-2 focus:ring-brandBlue outline-none transition-colors"></textarea>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end">
            <button type="submit" class="bg-brandBlue text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-sm flex items-center gap-2">
                <i class="ph-bold ph-floppy-disk"></i> Rekam Transaksi
            </button>
        </div>
    </form>
</main>
@endsection
@push('scripts')
<script>
    gsap.from(".gsap-fade", { opacity: 0, y: 20, duration: 0.6, stagger: 0.1, ease: "power2.out" });

    const categories = {
        'IN': [
            { value: 'Pendapatan Pajak', label: 'Pendapatan Pajak' },
            { value: 'Pendapatan Non-Pajak', label: 'Pendapatan Non-Pajak' },
            { value: 'Lelang SBN', label: 'Lelang SBN (Surat Berharga Negara)' }
        ],
        'OUT': [
            { value: 'Belanja Pusat', label: 'Belanja Pusat' },
            { value: 'TKDD', label: 'TKDD (Transfer ke Daerah & Dana Desa)' },
            { value: 'Dana Desa', label: 'Dana Desa' }
        ]
    };

    window.updateCategories = () => {
        const type = document.getElementById('typeSelect').value;
        const catSelect = document.getElementById('categorySelect');
        
        catSelect.innerHTML = '<option value="">-- Pilih Kategori --</option>';
        
        if (type && categories[type]) {
            catSelect.disabled = false;
            categories[type].forEach(cat => {
                const opt = document.createElement('option');
                opt.value = cat.value;
                opt.textContent = cat.label;
                catSelect.appendChild(opt);
            });
        } else {
            catSelect.disabled = true;
            catSelect.innerHTML = '<option value="">-- Pilih Tipe Terlebih Dahulu --</option>';
        }
    };
</script>
@endpush
