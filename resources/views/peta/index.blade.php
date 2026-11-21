
@extends('layouts.public')

@section('title', 'Peta Sekolah')

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
</div>

<div id="peta" class="peta"></div>
@endsection

@push('scripts')
<script>
    // Membuat peta
    const map = L.map('peta').setView([-3.3194, 114.5908], 12);

    // Menampilkan peta OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Membuat kelompok marker
    const lapisan = L.layerGroup().addTo(map);

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

            const fitur = data.features || [];

            lapisan.clearLayers();

            fitur.forEach(function(f) {
                const koordinat = f.geometry.coordinates;

                const longitude = koordinat[0];
                const latitude = koordinat[1];

                const p = f.properties;

                const marker = L.marker([latitude, longitude]);

                const popup = `
                    <b>${p.nama || 'Nama sekolah'}</b><br>
                    NPSN: ${p.npsn || '-'}<br>
                    Jenjang: ${p.jenjang || '-'}<br>
                    Status: ${p.status || '-'}<br>
                    Alamat: ${p.alamat || '-'}<br>
                    Kecamatan: ${p.kecamatan || '-'}<br>
                    <br>
                    <a href="/sekolah/${f.id}/profil-publik">
                        Lihat Profil Sekolah
                    </a>
                `;

                marker.bindPopup(popup);
                marker.addTo(lapisan);
            });

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
