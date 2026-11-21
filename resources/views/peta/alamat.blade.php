@extends('layouts.public')

@section('title', 'Cek Lokasi')

@section('content')

<div class="toolbar">
    <span>Klik peta atau gunakan tombol Lokasi Saya untuk mencari sekolah terdekat.</span>

    <button type="button" id="btn-lokasi">
        Lokasi Saya
    </button>

    <span id="status-lokasi"></span>
</div>

<div id="peta" class="peta"></div>

<div id="hasil" class="panel"
     style="top: 64px; right: 12px; width: 320px; max-height: 70%;
            overflow: auto; display: none;"></div>

@endsection

@push('scripts')

<script>
    const map = Peta.buat('peta');
    const lapisan = L.layerGroup().addTo(map);
    const panel = document.getElementById('hasil');

    const tombolLokasi = document.getElementById('btn-lokasi');
    const statusLokasi = document.getElementById('status-lokasi');

    let semua = [];
    let terakhir = 0;

    // Mengambil seluruh data sekolah
    Peta.ambilSekolah().then((fitur) => {
        semua = fitur;
    }).catch((err) => {
        console.error(err);
        statusLokasi.textContent = 'Gagal mengambil data sekolah';
    });

    // Mencari sekolah terdekat
    function terdekat(titik, n = 3) {
        return semua
            .map((f) => ({
                fitur: f,
                jarak: Peta.jarakKm(titik, Peta.latlng(f))
            }))
            .sort((a, b) => a.jarak - b.jarak)
            .slice(0, n);
    }

    // Mengambil alamat dari koordinat
    async function ambilAlamat(lat, lng) {
        const url = 'https://nominatim.openstreetmap.org/reverse'
            + '?format=jsonv2&addressdetails=1&accept-language=id'
            + `&lat=${lat}&lon=${lng}`;

        const respon = await fetch(url);

        if (!respon.ok) {
            throw new Error('HTTP ' + respon.status);
        }

        return respon.json();
    }

    // Menampilkan lokasi dan sekolah terdekat
    async function tampilkanLokasi(lat, lng) {

        const titik = [lat, lng];

        // Menghapus marker sebelumnya
        lapisan.clearLayers();

        // Marker lokasi pengguna
        L.circleMarker(titik, {
            radius: 9,
            color: '#fff',
            weight: 3,
            fillColor: '#ef4444',
            fillOpacity: 1,
        }).addTo(lapisan);

        // Mencari tiga sekolah terdekat
        const dekat = terdekat(titik);

        dekat.forEach((d) => {
            L.polyline([titik, Peta.latlng(d.fitur)], {
                color: '#2563eb',
                weight: 2,
                dashArray: '6 6',
            }).addTo(lapisan);
        });

        panel.style.display = 'block';
        panel.textContent = 'Mencari alamat...';

        // Mengambil alamat
        let bagianAlamat = '<em>Alamat tidak dapat dimuat.</em>';

        try {
            const data = await ambilAlamat(lat, lng);
            const a = data.address || {};

            const baris = [
                ['Jalan', a.road],
                ['Kelurahan/Desa', a.suburb || a.neighbourhood || a.village],
                ['Kota/Kabupaten', a.city || a.town || a.municipality || a.county],
                ['Provinsi', a.state],
                ['Kode pos', a.postcode],
            ].map(([k, v]) => `
                <tr>
                    <td style="padding: 2px 8px 2px 0; color: #6b7280;">${k}</td>
                    <td>${Peta.esc(v) || '-'}</td>
                </tr>
            `).join('');

            bagianAlamat = `
                <p style="margin: 6px 0;">
                    ${Peta.esc(data.display_name || '-')}
                </p>
                <table style="font-size: 13px;">
                    ${baris}
                </table>
            `;

        } catch (err) {
            console.error(err);
        }

        // Membuat daftar sekolah terdekat
        const daftar = dekat.map((d) => `
            <li data-npsn="${Peta.esc(d.fitur.properties.npsn)}"
                style="padding: 6px 0; border-top: 1px solid #e5e7eb; cursor: pointer;">
                <strong>${Peta.esc(d.fitur.properties.nama)}</strong><br>
                <small>${d.jarak.toFixed(2)} km (garis lurus)</small>
            </li>
        `).join('');

        // Menampilkan hasil pada panel
        panel.innerHTML = `
            <strong>Koordinat</strong>
            <div style="font-family: monospace;">
                ${lat.toFixed(6)}, ${lng.toFixed(6)}
            </div>

            <strong style="display: block; margin-top: 8px;">Alamat</strong>
            ${bagianAlamat}

            <strong style="display: block; margin-top: 8px;">
                Sekolah terdekat
            </strong>

            <ul style="list-style: none; margin: 4px 0 0; padding: 0;">
                ${daftar || '<li>Data sekolah tidak tersedia.</li>'}
            </ul>
        `;
    }

    // Klik pada peta untuk mengecek lokasi
    map.on('click', async (e) => {

        const sekarang = Date.now();

        // Membatasi permintaan Nominatim
        if (sekarang - terakhir < 1200) return;

        terakhir = sekarang;

        await tampilkanLokasi(e.latlng.lat, e.latlng.lng);
    });

    // Tombol Lokasi Saya
    tombolLokasi.addEventListener('click', () => {

        if (!navigator.geolocation) {
            statusLokasi.textContent =
                'Browser tidak mendukung geolocation.';
            return;
        }

        if (semua.length === 0) {
            statusLokasi.textContent =
                'Data sekolah masih dimuat. Coba lagi.';
            return;
        }

        statusLokasi.textContent = 'Mengambil lokasi...';
        tombolLokasi.disabled = true;

        navigator.geolocation.getCurrentPosition(

            async (posisi) => {

                const lat = posisi.coords.latitude;
                const lng = posisi.coords.longitude;

                // Memusatkan peta ke lokasi pengguna
                map.setView([lat, lng], 15);

                // Menampilkan lokasi dan sekolah terdekat
                await tampilkanLokasi(lat, lng);

                statusLokasi.textContent = 'Lokasi berhasil ditemukan.';
                tombolLokasi.disabled = false;

            },

            (error) => {

                let pesan = 'Gagal mendapatkan lokasi.';

                if (error.code === 1) {
                    pesan = 'Izin lokasi ditolak. Aktifkan izin lokasi browser.';
                } else if (error.code === 2) {
                    pesan = 'Informasi lokasi tidak tersedia.';
                } else if (error.code === 3) {
                    pesan = 'Waktu mengambil lokasi habis. Coba lagi.';
                }

                statusLokasi.textContent = pesan;
                tombolLokasi.disabled = false;

            },

            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            }

        );

    });

    // Klik sekolah pada daftar untuk melihat lokasinya
    panel.addEventListener('click', (e) => {

        const li = e.target.closest('[data-npsn]');

        if (!li) return;

        const f = semua.find(
            (x) => x.properties.npsn === li.dataset.npsn
        );

        if (f) {
            L.popup()
                .setLatLng(Peta.latlng(f))
                .setContent(Peta.popup(f))
                .openOn(map);

            map.flyTo(Peta.latlng(f), 17);
        }

    });

</script>

@endpush
