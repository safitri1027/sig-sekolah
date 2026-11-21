
@extends('layouts.public')

@section('title', 'Peta Panas')

@section('content')

<div class="toolbar">
    <label>Bobot
        <select id="mode">
            <option value="sekolah">Jumlah sekolah</option>
            <option value="siswa">Jumlah siswa</option>
        </select>
    </label>

    <label>Jenjang
        <select id="jenjang">
            <option value="">Semua jenjang</option>
            <option value="SD">SD</option>
            <option value="SMP">SMP</option>
            <option value="SMA">SMA</option>
            <option value="SMK">SMK</option>
        </select>
    </label>

    <label>Radius
        <input type="range" id="radius" min="10" max="60" value="30">
    </label>

    <span id="jumlah"></span>
</div>

<div id="peta" class="peta"></div>

<div class="panel" style="left: 12px; bottom: 24px; width: 180px;">
    <div style="height: 10px; border-radius: 5px;
        background: linear-gradient(to right, #2563eb, #06b6d4, #84cc16, #facc15, #dc2626);">
    </div>

    <div style="display: flex; justify-content: space-between;
        font-size: 12px; margin-top: 4px;">
        <span>Rendah</span>
        <span>Tinggi</span>
    </div>
</div>

@endsection

@push('scripts')

<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>

<script>
    // Membuat peta
    const map = L.map('peta').setView([-3.3194, 114.5908], 12);

    // Menampilkan OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let heat = null;
    let data = [];

    // Menggambar heatmap
    function gambar() {
        const mode = document.getElementById('mode').value;

        const radius = Number(
            document.getElementById('radius').value
        );

        const maks = Math.max(
            1,
            ...data.map(function(f) {
                return Number(f.properties.jumlah_siswa || 0);
            })
        );

        const titik = data.map(function(f) {
            const koordinat = f.geometry.coordinates;

            const latitude = koordinat[1];
            const longitude = koordinat[0];

            const siswa = Number(f.properties.jumlah_siswa || 0);

            const bobot = mode === 'siswa'
                ? (siswa > 0 ? siswa / maks : 0.1)
                : 0.35;

            return [latitude, longitude, bobot];
        });

        // Menghapus heatmap sebelumnya
        if (heat) {
            map.removeLayer(heat);
        }

        // Membuat heatmap baru
        heat = L.heatLayer(titik, {
            radius: radius,
            blur: 25,
            maxZoom: 17,
            max: 1.0,

            gradient: {
                0.2: '#2563eb',
                0.4: '#06b6d4',
                0.6: '#84cc16',
                0.8: '#facc15',
                1.0: '#dc2626'
            }
        }).addTo(map);

        document.getElementById('jumlah').textContent =
            data.length + ' sekolah';
    }

    // Memuat data sekolah
    async function muat() {
        try {
            const jenjang =
                document.getElementById('jenjang').value;

            let url = '/data/sekolah';

            if (jenjang) {
                url += '?jenjang=' + encodeURIComponent(jenjang);
            }

            const response = await fetch(url);

            if (!response.ok) {
                throw new Error('Gagal mengambil data sekolah');
            }

            const hasil = await response.json();

            data = (hasil.features || []).filter(function(f) {
                return f.geometry &&
                    f.geometry.coordinates &&
                    f.geometry.coordinates.length >= 2;
            });

            gambar();

            if (data.length > 0) {
                const batas = L.latLngBounds(
                    data.map(function(f) {
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

    // Filter jenjang
    document.getElementById('jenjang')
        .addEventListener('change', muat);

    // Mengubah bobot
    document.getElementById('mode')
        .addEventListener('change', gambar);

    // Mengubah radius
    document.getElementById('radius')
        .addEventListener('input', gambar);

    // Menjalankan peta
    muat();
</script>

@endpush
