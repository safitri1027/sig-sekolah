<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Profil {{ $sekolah->nama }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 30px 15px;
            color: #333;
        }

        .container {
            max-width: 850px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
        }

        h1 {
            color: #166534;
            margin-bottom: 10px;
        }

        h2 {
            margin-top: 30px;
            color: #166534;
        }

        .item {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .label {
            font-weight: bold;
        }

        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            background: #166534;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>
<body>

<div class="container">

    <h1>{{ $sekolah->nama }}</h1>

    <p>{{ $sekolah->jenjang }} - {{ $sekolah->status }}</p>

    <h2>Informasi Sekolah</h2>

    <div class="item">
        <span class="label">NPSN:</span>
        {{ $sekolah->npsn }}
    </div>

    <div class="item">
        <span class="label">Alamat:</span>
        {{ $sekolah->alamat }}
    </div>

    <div class="item">
        <span class="label">Kecamatan:</span>
        {{ $sekolah->kecamatan }}
    </div>

    <h2>Profil Sekolah</h2>

    @if ($sekolah->profil)
        <div class="item">
            <span class="label">Kepala Sekolah:</span>
            {{ $sekolah->profil->kepala_sekolah ?? '-' }}
        </div>

        <div class="item">
            <span class="label">Email:</span>
            {{ $sekolah->profil->email ?? '-' }}
        </div>

        <div class="item">
            <span class="label">Website:</span>
            @if ($sekolah->profil->website)
                <a href="{{ $sekolah->profil->website }}" target="_blank">
                    {{ $sekolah->profil->website }}
                </a>
            @else
                -
            @endif
        </div>

        <div class="item">
            <span class="label">Tahun Berdiri:</span>
            {{ $sekolah->profil->tahun_berdiri ?? '-' }}
        </div>

        <div class="item">
            <span class="label">Akreditasi:</span>
            {{ $sekolah->profil->akreditasi ?? '-' }}
        </div>

        <div class="item">
            <span class="label">Jumlah Siswa:</span>
            {{ $sekolah->profil->jumlah_siswa ?? '-' }}
        </div>

        <div class="item">
            <span class="label">Jumlah Guru:</span>
            {{ $sekolah->profil->jumlah_guru ?? '-' }}
        </div>

        <div class="item">
            <span class="label">Deskripsi:</span>
            {{ $sekolah->profil->deskripsi ?? '-' }}
        </div>
    @else
        <p>Profil sekolah belum dilengkapi.</p>
    @endif

    <a href="{{ route('peta.index') }}" class="btn">
        Kembali ke Peta
    </a>

</div>

</body>
</html>