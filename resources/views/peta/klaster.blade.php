
@extends('layouts.public')

@section('title', 'Klaster')

@push('styles')
<link rel="stylesheet"
    href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css">

<link rel="stylesheet"
    href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css">
@endpush
@section('content')

<div class="toolbar">
    <label>Jenjang
        <select id="filter-jenjang">
            <option value="">Semua jenjang</option>
            <option value="SD">SD</option>
            <option value="SMP">SMP</option>
            <option value="SMA">SMA</option>
            <option value="SMK">SMK</option>
        </select>
    </label>

    <span id="jumlah"></span>

    <span style="color: #6b7280;">
        Klik angka pada klaster untuk memperbesar peta.
    </span>
</div>

<div id="peta" class="peta"></div>

@endsection

@push('scripts')

<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

<script>
    // Membuat peta
    const map = L.map('peta').setView([-3.3194, 114.5908], 12);

    // Menampilkan OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Membuat kelompok marker
    const klaster = L.markerClusterGroup({
        showCoverageOnHover: false,
        maxClusterRadius: 60,
        spiderfyOnMaxZoom: true,
        disableClusteringAtZoom: 17,
        chunkedLoading: true
    });

    map.addLayer(klaster);

    // Memuat data sekolah
    async function muat(jenjang = '') {
        try {
            let url = '/data/sekolah';

            if (jenjang) {
                url += '?jenjang=' + encodeURIComponent(jenjang);
            }

            const response = await fetch(url);

            if (!response.ok) {
                throw new Error('Gagal mengambil data sekolah');
            }

            const data = await response.json();

            const fitur = (data.features || []).filter(function(f) {
                return f.geometry &&
                    f.geometry.coordinates &&
                    f.geometry.coordinates.length >= 2;
            });

            klaster.clearLayers();

            const markerList = fitur.map(function(f) {
                const koordinat = f.geometry.coordinates;

                const latitude = koordinat[1];
                const longitude = koordinat[0];

                const p = f.properties;

                const marker = L.marker([latitude, longitude]);

                marker.bindPopup(`
                    <b>${p.nama || '-'}</b><br>
                    NPSN: ${p.npsn || '-'}<br>
                    Jenjang: ${p.jenjang || '-'}<br>
                    Status: ${p.status || '-'}<br>
                    Alamat: ${p.alamat || '-'}<br>
                    Kecamatan: ${p.kecamatan || '-'}<br><br>
                    <a href="/sekolah/${f.id}/profil-publik">
                        Lihat Profil Sekolah
                    </a>
                `);

                return marker;
            });

            klaster.addLayers(markerList);

            document.getElementById('jumlah').textContent =
                fitur.length + ' sekolah';

            if (fitur.length > 0) {
                const batas = L.latLngBounds(
                    fitur.map(function(f) {
                        return [
                            f.geometry.coordinates[1],
                            f.geometry.coordinates[0]
                        ];
                    })
                );

                map.fitBounds(batas.pad(0.1));
            }

        } catch (error) {
            console.error(error);

            document.getElementById('jumlah').textContent =
                'Gagal memuat data sekolah';
        }
    }

    // Filter berdasarkan jenjang
    document.getElementById('filter-jenjang')
        .addEventListener('change', function(e) {
            muat(e.target.value);
        });

    // Menampilkan seluruh sekolah
    muat();
</script>

@endpush
