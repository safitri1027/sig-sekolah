
window.Peta = {
    buat(id) {
        const map = L.map(id).setView([-3.3194, 114.5908], 11);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        return map;
    },

    async ambilSekolah(jenjang = '') {
        const url = `${window.PETA_URL.data}?jenjang=${encodeURIComponent(jenjang)}`;

        const response = await fetch(url);

        if (!response.ok) {
            throw new Error('Gagal mengambil data sekolah');
        }

        const data = await response.json();

        return data.features;
    },

    latlng(f) {
        const koordinat = f.geometry.coordinates;

        return [koordinat[1], koordinat[0]];
    },

    esc(teks) {
        return String(teks ?? '').replace(/[&<>"']/g, karakter => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[karakter]);
    },

    popup(f) {
        const p = f.properties;

        return `
            <strong>${this.esc(p.nama)}</strong><br>
            NPSN: ${this.esc(p.npsn)}<br>
            Jenjang: ${this.esc(p.jenjang)}<br>
            Kecamatan: ${this.esc(p.kecamatan)}
        `;
    }
};
