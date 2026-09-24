@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<header class="max-w-7xl mx-auto mb-12 mt-8 gsap-fade flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        @if($user->role === 'super_admin')
            <h1 class="text-4xl md:text-5xl font-bold tracking-tight mb-3">Pusat Komando Keuangan Nasional</h1>
            <p class="text-textSecondary text-lg">Selamat datang, Yang Mulia Menteri. Berikut ringkasan APBN Nasional.</p>
        @elseif($user->role === 'gubernur')
            <h1 class="text-4xl md:text-5xl font-bold tracking-tight mb-3">Dashboard Daerah ({{ \DB::table('regions')->where('id', $user->region_id)->value('name') }})</h1>
            <p class="text-textSecondary text-lg">Selamat datang, Bapak/Ibu Gubernur. Berikut ringkasan alokasi wilayah Anda.</p>
        @else
            <h1 class="text-4xl md:text-5xl font-bold tracking-tight mb-3">Tinjauan Keuangan Nasional</h1>
            <p class="text-textSecondary text-lg">Portal Auditor. Pembaruan data APBN secara waktu nyata.</p>
        @endif
    </div>
    
    <div>
        <button id="btn-simulate" onclick="simulateTransaction()" class="flex items-center gap-2 bg-textPrimary text-white dark:bg-white dark:text-gray-900 px-5 py-2.5 rounded-full font-medium text-sm hover:opacity-80 transition-opacity shadow-soft">
            <i class="ph-bold ph-play"></i> Mode Simulasi (Demo)
        </button>
    </div>
</header>

<main class="max-w-7xl mx-auto">
    <!-- Top Row: Chart & Sentinel/Forecast -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6 gsap-fade">
        <div class="lg:col-span-2 bento-card p-6 h-[400px] flex flex-col relative overflow-hidden">
            <div class="flex justify-between items-center mb-4 z-10 relative">
                <h3 class="font-bold text-lg text-textPrimary dark:text-darkText">
                    {{ $user->role === 'gubernur' ? 'Arus Kas Masuk Wilayah (Live Tick)' : 'Arus Kas Nasional (Live Tick)' }}
                </h3>
                <div class="flex items-center gap-2 text-xs font-semibold px-3 py-1 bg-green-50 text-accentGreen rounded-full border border-green-200">
                    <span class="w-2 h-2 rounded-full bg-accentGreen animate-pulse"></span> SYSTEM ONLINE
                </div>
            </div>
            <div id="liveVelocityChart" class="flex-1 w-full relative z-10 -ml-2"></div>
            @if($anomalies > 0 && $user->role !== 'gubernur')
                <div class="absolute inset-0 border-4 border-red-500/20 rounded-2xl pointer-events-none animate-pulse z-0"></div>
            @endif
        </div>
        
        <!-- Right Panel -->
        <div class="bento-card p-6 flex flex-col gap-4">
            @if($user->role === 'gubernur')
                <!-- Governor Right Panel -->
                <div class="flex items-center gap-2 mb-2">
                    <i class="ph-fill ph-map-pin text-brandBlue text-2xl"></i>
                    <h3 class="font-bold text-lg text-textPrimary dark:text-darkText">Ringkasan Wilayah</h3>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-900/50">
                    <div class="text-xs font-bold text-brandBlue mb-1">TOTAL DANA DITERIMA (TKDD + DESA)</div>
                    <div class="text-3xl font-black text-textPrimary dark:text-darkText mb-1">
                        @php 
                           $totalDaerah = str_replace(',','.',$tkdd_t) + str_replace(',','.',$dana_desa_t);
                        @endphp
                        Rp {{ number_format($totalDaerah, 1, ',', '.') }} T
                    </div>
                </div>
                <div class="flex justify-between items-center text-sm bg-gray-50 dark:bg-darkBg p-3 rounded-lg mt-auto">
                    <span class="font-semibold">Dana Desa Dialokasikan</span>
                    <span class="font-mono text-textSecondary font-bold">Rp {{ $dana_desa_t }} T</span>
                </div>
                <div class="flex justify-between items-center text-sm bg-gray-50 dark:bg-darkBg p-3 rounded-lg">
                    <span class="font-semibold">Dana TKDD Dialokasikan</span>
                    <span class="font-mono text-textSecondary font-bold">Rp {{ $tkdd_t }} T</span>
                </div>
            @else
                <!-- National Right Panel -->
                <div class="flex items-center gap-2 mb-2">
                    <i class="ph-fill ph-brain text-purple-500 text-2xl"></i>
                    <h3 class="font-bold text-lg text-textPrimary dark:text-darkText">AI Forecasting & Sentinel</h3>
                </div>
                <p class="text-sm text-textSecondary leading-relaxed">
                    Sentinel AI menganalisis laju pembakaran uang kas (burn rate) saat ini sebesar <b>Rp 12,4 Triliun / jam</b>. 
                </p>
                <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-xl border border-purple-100 dark:border-purple-800">
                    <div class="text-xs font-bold text-purple-700 dark:text-purple-400 mb-1">PROYEKSI 30 HARI KE DEPAN</div>
                    <div class="font-mono text-sm text-textPrimary dark:text-gray-300">Jika tren belanja infrastruktur KEMENPUPR konstan, defisit APBN bulan ini diprediksi menyentuh angka aman <b class="text-accentRed">2.14%</b> PDB (Batas UU: 3%).</div>
                </div>
                <div class="mt-auto">
                    <a href="/approvals" class="flex justify-between items-center bg-gray-50 dark:bg-darkBg p-3 rounded-lg hover:border-brandBlue transition-colors group border border-gray-100 dark:border-gray-800">
                        <span class="font-semibold text-sm">Menunggu Otorisasi</span>
                        <span class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded text-xs font-bold group-hover:bg-yellow-200">{{ $pendingCount }} Draf</span>
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- BI SECTION -->
    <div class="grid grid-cols-1 {{ $user->role === 'gubernur' ? 'lg:grid-cols-1' : 'lg:grid-cols-2' }} gap-6 mb-6 gsap-fade">
        <div class="bento-card p-6 h-[400px] flex flex-col">
            <h3 class="font-bold text-lg text-textPrimary dark:text-darkText mb-4">
                {{ $user->role === 'gubernur' ? 'Proporsi Jenis Penerimaan Anggaran Daerah' : 'Distribusi Kategori Belanja Nasional' }}
            </h3>
            <div id="pieChart" class="flex-1 w-full flex items-center justify-center"></div>
        </div>
        
        @if($user->role !== 'gubernur')
        <div class="bento-card p-6 h-[400px] flex flex-col">
            <h3 class="font-bold text-lg text-textPrimary dark:text-darkText mb-4">Top 5 Wilayah Alokasi Terbesar</h3>
            <div id="barChart" class="flex-1 w-full flex items-center justify-center -ml-2"></div>
        </div>
        @endif
    </div>

    <!-- Live Ticker Marquee -->
    <div class="bento-card flex items-center h-16 relative overflow-hidden group mb-12">
        <div class="absolute left-0 top-0 bottom-0 w-32 bg-white dark:bg-darkBg z-20 flex items-center px-6 border-r border-gray-100 dark:border-gray-800 shadow-[4px_0_12px_rgba(0,0,0,0.02)]">
            <span class="flex items-center gap-2 text-xs font-bold text-brandBlue uppercase tracking-wider">
                <span class="w-1.5 h-1.5 bg-brandBlue rounded-full animate-ping absolute"></span>
                <span class="w-1.5 h-1.5 bg-brandBlue rounded-full relative"></span>
                Live
            </span>
        </div>
        <div class="ticker text-sm text-textPrimary dark:text-darkText pl-40" id="live-ticker-container">
            <div class="flex gap-16 items-center pt-5" id="ticker-content"><span class="text-textSecondary">Mengambil data...</span></div>
            <div class="flex gap-16 items-center pt-5 ml-16" id="ticker-content-clone"></div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    gsap.from(".gsap-fade", { opacity: 0, y: 20, duration: 0.8, ease: "power2.out" });
    gsap.from(".gsap-bento > div", { opacity: 0, y: 30, duration: 0.6, stagger: 0.08, ease: "power2.out", delay: 0.2 });

    const formatMoney = (amount) => {
        if(amount >= 1e12) return 'Rp ' + (amount / 1e12).toFixed(1) + 'T';
        if(amount >= 1e9) return 'Rp ' + (amount / 1e9).toFixed(1) + 'M';
        return 'Rp ' + amount;
    };

    // Live ApexChart Setup
    let chartData = Array(15).fill(0);
    const options = {
        series: [{ name: 'Arus Kas Masuk (Triliun)', data: chartData }],
        chart: { type: 'area', height: '100%', sparkline: { enabled: true }, animations: { enabled: true, easing: 'linear', dynamicAnimation: { speed: 1000 } } },
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0, stops: [0, 100] } },
        colors: ['#0066CC'],
        yaxis: { min: 0 }
    };
    const chart = new ApexCharts(document.querySelector("#liveVelocityChart"), options);
    chart.render();

    const fetchTickerData = async () => {
        try {
            const res = await axios.get('/api/live-ticker');
            const html = res.data.map(trx => {
                const time = new Date(trx.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit', second:'2-digit'});
                let color = trx.type === 'IN' ? 'text-accentGreen' : 'text-brandBlue';
                if (trx.status === 'Anomaly') color = 'text-accentRed font-bold';
                const sign = trx.type === 'IN' ? '+' : '-';
                const icon = trx.status === 'Anomaly' ? '<i class="ph-fill ph-warning"></i> ' : '';
                return `<span class="flex items-center gap-3"><span class="text-textSecondary font-mono text-xs">${time}</span> <span class="font-semibold ${trx.status === 'Anomaly' ? 'text-accentRed' : ''}">${icon}${trx.category}</span> <span class="${color} font-mono">${sign}${formatMoney(trx.amount)}</span></span>`;
            }).join(' <span class="text-gray-300 px-4"> </span> ');
            document.getElementById('ticker-content').innerHTML = html;
            document.getElementById('ticker-content-clone').innerHTML = html;

            // Update chart data based on latest transactions (mock flow)
            let latestAmountT = res.data[0] ? (res.data[0].amount / 1e12) : 0;
            chartData.push(latestAmountT);
            chartData.shift();
            chart.updateSeries([{ data: chartData }]);

        } catch(e) {}
    };
    fetchTickerData();
    setInterval(fetchTickerData, 1500); // Polling faster for live stream feel

    window.simulateTransaction = async () => {
        try {
            const btn = document.getElementById('btn-simulate');
            if(btn) btn.innerHTML = '<i class="ph-bold ph-spinner animate-spin"></i> Memproses...';
            await axios.get('/api/simulate-transaction');
            await fetchTickerData(); 
            if(btn) btn.innerHTML = '<i class="ph-bold ph-check"></i> Data Masuk!';
            setTimeout(() => window.location.reload(), 1500);
        } catch(e) {}
    };

    // Render BI Pie Chart
    const expenseData = @json($expenseByCategory);
    if(expenseData.length > 0) {
        new ApexCharts(document.querySelector("#pieChart"), {
            series: expenseData.map(e => parseFloat(e.total)),
            labels: expenseData.map(e => e.category),
            chart: { type: 'donut', height: '100%', fontFamily: 'Inter' },
            colors: ['#0066CC', '#34C759', '#FF9F0A', '#FF3B30', '#AF52DE'],
            plotOptions: { pie: { donut: { size: '75%', labels: { show: true, name: { show: true }, value: { formatter: val => 'Rp ' + (val/1e12).toFixed(1) + ' T' } } } } },
            dataLabels: { enabled: false },
            stroke: { show: false },
            legend: { position: 'bottom' }
        }).render();
    }

    // Render BI Bar Chart
    const regionData = @json($topRegions);
    if(regionData.length > 0) {
        new ApexCharts(document.querySelector("#barChart"), {
            series: [{ name: 'Alokasi', data: regionData.map(r => parseFloat((r.total/1e12).toFixed(1))) }],
            chart: { type: 'bar', height: '100%', fontFamily: 'Inter', toolbar: { show: false } },
            plotOptions: { bar: { horizontal: true, borderRadius: 4, distributed: true } },
            colors: ['#0066CC', '#3385D6', '#66A3E0', '#99C2EB', '#CCE0F5'],
            dataLabels: { enabled: true, formatter: val => val + ' T', style: { colors: ['#fff'] } },
            xaxis: { categories: regionData.map(r => r.name), labels: { formatter: val => val + ' T' } },
            legend: { show: false },
            grid: { strokeDashArray: 4 }
        }).render();
    }
</script>
@endpush
