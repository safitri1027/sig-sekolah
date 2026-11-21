@extends('layouts.public')

@section('title', 'Pencarian')

@push('styles')
<style>
    #hasil {
        list-style: none;
        margin: 8px 0 0;
        padding: 0;
    }

    #hasil li {
        padding: 8px 4px;
        border-top: 1px solid #e5e7eb;
        cursor: pointer;
    }

    #hasil li:hover {
        background: #eff6ff;
    }
</style>
@endpush

@section('content')

<div class="toolbar">
    <input type="search" id="kata" size="32"
        placeholder="Cari nama, NPSN, atau kecamatan">

    <select id="jenjang">
        <option value="">Semua jenjang</option>
        <option value="SD">SD</option>
        <option value="SMP">SMP</option>
        <option value="SMA">SMA</option>
        <option value="SMK">SMK</option>
    </select>

    <button id="hapus" type="button">Hapus</button>

    <span id="jumlah"></span>
</div>

<div id="peta" class="peta"></div>

<div class="panel"
    style="top: 64px; left: 60px; width: 280px; max-height: 55%; overflow: auto;">

    <strong>Hasil pencarian</strong>
    <ul id="hasil"></ul>
</div>

@endsection

@push('scripts')
<script>
    // Membuat peta Leaflet
    const map = L.map('peta').setView([-3.3194, 114.5908], 12);

    // Menampilkan OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const lapisan = L.layerGroup().addTo(map);

    let semua = [];

    // Mengambil koordinat sekolah
    function koordinat(f) {
        return [
            f.geometry.coordinates[1],
            f.geometry.coordinates[0]
        ];
    }

    // Menampilkan hasil pencarian
    function tampil(daftar) {
        lapisan.clearLayers();

        const ul = document.getElementById('hasil');
        ul.innerHTML = '';

        daftar.forEach(function(f) {
            const p = f.properties;
            const posisi = koordinat(f);

            const marker = L.marker(posisi).addTo(lapisan);

            marker.bindPopup(`
                <b>${p.nama || '-'}</b><br>
                NPSN: ${p.npsn || '-'}<br>
                Jenjang: ${p.jenjang || '-'}<br>
                Status: ${p.status || '-'}<br>
                Alamat: ${p.alamat || '-'}<br>
                Kecamatan: ${p.kecamatan || '-'}
            `);

            const li = document.createElement('li');

            li.textContent =
                `${p.nama || '-'} (${p.kecamatan || '-'})`;

            li.addEventListener('click', function() {
                map.flyTo(posisi, 17);
                marker.openPopup();
            });

            ul.appendChild(li);
        });

        document.getElementById('jumlah').textContent =
            `${daftar.length} dari ${semua.length} sekolah`;

        if (daftar.length > 0) {
            const batas = L.latLngBounds(
                daftar.map(koordinat)
            );

            map.fitBounds(batas.pad(0.1));
        }
    }

    // Fungsi pencarian
    function cari() {
        const kata = document.getElementById('kata')
            .value.trim().toLowerCase();

        const jenjang = document.getElementById('jenjang').value;

        const hasil = semua.filter(function(f) {
            const p = f.properties;

            const cocokJenjang =
                !jenjang || p.jenjang === jenjang;

            const cocokKata =
                !kata ||
                [p.nama, p.npsn, p.kecamatan].some(function(t) {
                    return String(t || '').toLowerCase().includes(kata);
                });

            return cocokJenjang && cocokKata;
        });

        tampil(hasil);
    }

    // Pencarian berdasarkan kata
    let tunda;

    document.getElementById('kata').addEventListener('input', function() {
        clearTimeout(tunda);
        tunda = setTimeout(cari, 300);
    });

    // Filter jenjang
    document.getElementById('jenjang')
        .addEventListener('change', cari);

    // Tombol hapus
    document.getElementById('hapus')
        .addEventListener('click', function() {
            document.getElementById('kata').value = '';
            document.getElementById('jenjang').value = '';
            cari();
        });

    // Mengambil data sekolah dari GeoJSON
    async function muatSekolah() {
        try {
            const response = await fetch('/data/sekolah');

            if (!response.ok) {
                throw new Error('Gagal mengambil data sekolah');
            }

            const data = await response.json();

            semua = (data.features || []).filter(function(f) {
                return f.geometry &&
                    f.geometry.coordinates &&
                    f.geometry.coordinates.length >= 2;
            });

            tampil(semua);

        } catch (error) {
            console.error(error);

            document.getElementById('jumlah').textContent =
                'Gagal memuat data sekolah';
        }
    }

    muatSekolah();
</script>
@endpush
