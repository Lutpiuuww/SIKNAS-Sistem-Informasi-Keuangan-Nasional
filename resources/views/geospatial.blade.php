@extends('layouts.app')
@section('title', 'Peta Geospasial')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<style>
    .leaflet-popup-content-wrapper { border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
</style>
@endpush

@section('content')
<header class="max-w-7xl mx-auto mb-12 mt-8 gsap-fade">
    <h1 class="text-4xl font-bold tracking-tight mb-3">Distribusi Geospasial</h1>
    @if(Auth::user()->role === 'gubernur')
        <p class="text-textSecondary text-lg">Peta interaktif penyaluran anggaran untuk provinsi Anda.</p>
    @else
        <p class="text-textSecondary text-lg">Peta interaktif penyaluran Dana Desa dan Transfer Daerah 38 Provinsi.</p>
    @endif
</header>
<main class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6 gsap-fade">
    <div class="lg:col-span-2 bento-card p-2 h-[600px] relative overflow-hidden">
        <div id="map" class="w-full h-full rounded-2xl z-10"></div>
    </div>
    <div class="bento-card flex flex-col h-[600px] p-6">
        <h3 class="font-bold text-lg mb-3">Alokasi Wilayah (Live)</h3>
        
        <div class="relative mb-4">
            <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" id="searchRegion" onkeyup="filterRegions()" placeholder="Cari provinsi..." class="w-full pl-9 pr-4 py-2 bg-gray-50 dark:bg-darkBg border border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brandBlue text-textPrimary dark:text-darkText transition-colors">
        </div>

        <div class="flex-1 overflow-y-auto pr-2 flex flex-col gap-4 scrollbar-thin" id="regionList">
            @foreach($regions as $r)
            <div class="region-item bg-bgLight p-4 rounded-xl hover:shadow-md transition-shadow cursor-pointer border border-transparent hover:border-gray-200" data-name="{{ strtolower($r->name) }}" onclick="focusMap({{ $r->lat ?? -2.5489 }}, {{ $r->lng ?? 118.0149 }})">
                <div class="text-xs text-textSecondary font-semibold mb-1 uppercase tracking-wider">{{ $r->type }}</div>
                <div class="font-medium mb-2 region-name">{{ $r->name }}</div>
                <div class="font-mono text-brandBlue font-bold text-lg">Rp {{ number_format($r->tkdd_allocated / 1000000000000, 1, ',', '.') }} T</div>
            </div>
            @endforeach
        </div>
    </div>
</main>
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    gsap.from(".gsap-fade", { opacity: 0, y: 20, duration: 0.8, stagger: 0.2, ease: "power2.out" });

    const regions = @json($regions);
    let centerLat = -2.5489;
    let centerLng = 118.0149;
    let zoomLevel = 5;

    @if(Auth::user()->role === 'gubernur')
        const province = regions.find(r => r.type === 'Provinsi');
        if (province) {
            centerLat = province.lat;
            centerLng = province.lng;
            zoomLevel = 9;
        }
    @endif

    const map = L.map('map', { attributionControl: false }).setView([centerLat, centerLng], zoomLevel);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(map);

    const markerMap = {}; // store markers by lat-lng key to pulse them

    regions.forEach(r => {
        if (r.lat && r.lng) {
            const tkdd = (r.tkdd_allocated / 1e12).toFixed(1).replace('.', ',');
            const marker = L.marker([r.lat, r.lng]).addTo(map)
             .bindPopup(`<div class="font-sans"><div class="text-xs text-gray-500 font-semibold uppercase mb-1">${r.type}</div><div class="font-bold text-sm mb-1">${r.name}</div><div class="font-mono text-blue-600 font-bold">Rp ${tkdd} T</div></div>`);
            
            // Generate a close enough key to map transactions to this region
            const key = Math.round(r.lat*10) + '_' + Math.round(r.lng*10);
            if(!markerMap[key]) markerMap[key] = [];
            markerMap[key].push({lat: r.lat, lng: r.lng, type: r.type});
        }
    });

    window.focusMap = (lat, lng) => {
        if(lat && lng) {
            map.flyTo([lat, lng], 10, { duration: 1.5 });
        }
    };

    // Live Map Pulse Logic
    let lastTrxId = 0;
    setInterval(async () => {
        try {
            const res = await axios.get('/api/live-ticker');
            if(res.data.length > 0) {
                const latest = res.data[0];
                if(latest.id !== lastTrxId && latest.lat && latest.lng) {
                    lastTrxId = latest.id;
                    const color = latest.type === 'IN' ? '#34C759' : (latest.status === 'Anomaly' ? '#FF3B30' : '#0066CC');
                    
                    let targetLat = latest.lat;
                    let targetLng = latest.lng;

                    // If Governor, pick a random Kabupaten in this province to pulse
                    @if(Auth::user()->role === 'gubernur')
                        const kabs = regions.filter(r => r.type === 'Kabupaten');
                        if (kabs.length > 0) {
                            const randomKab = kabs[Math.floor(Math.random() * kabs.length)];
                            targetLat = randomKab.lat;
                            targetLng = randomKab.lng;
                        }
                    @endif
                    
                    const pulse = L.circleMarker([targetLat, targetLng], {
                        radius: 8, fillColor: color, color: color, weight: 2, opacity: 1, fillOpacity: 0.8
                    }).addTo(map);

                    let r = 8;
                    const anim = setInterval(() => {
                        r += 2;
                        pulse.setRadius(r);
                        pulse.setStyle({ opacity: Math.max(0, 1 - (r/40)), fillOpacity: Math.max(0, 0.8 - (r/40)) });
                        if(r > 40) { clearInterval(anim); map.removeLayer(pulse); }
                    }, 50);
                }
            }
        } catch(e) {}
    }, 1500);

    window.filterRegions = () => {
        const input = document.getElementById('searchRegion').value.toLowerCase();
        const items = document.querySelectorAll('.region-item');
        items.forEach(item => {
            const name = item.getAttribute('data-name');
            if (name.includes(input)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    };
</script>
@endpush
