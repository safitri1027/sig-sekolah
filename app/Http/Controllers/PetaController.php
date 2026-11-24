<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\Request;

class PetaController extends Controller
{
    // Halaman utama Peta Sekolah
    public function index()
    {
        return view('peta.index');
    }

    // Halaman Klaster Sekolah
    public function klaster()
    {
        return view('peta.klaster');
    }

    // Halaman Peta Panas
    public function panas()
    {
        return view('peta.panas');
    }

    // Halaman Cek Lokasi
    public function alamat()
    {
        return view('peta.alamat');
    }

    // Mengambil data sekolah dalam format GeoJSON
    public function geojson(Request $request)
    {
        $features = Sekolah::with('profil')
            ->when($request->jenjang, function ($q, $j) {
                $q->where('jenjang', $j);
            })
            ->when($request->status, function ($q, $s) {
                $q->where('status', $s);
            })
            ->get()
            ->map(fn ($s) => [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [
                        $s->longitude,
                        $s->latitude
                    ],
                ],
                'properties' => [
                    'npsn' => $s->npsn,
                    'nama' => $s->nama,
                    'jenjang' => $s->jenjang,
                    'status' => $s->status,
                    'alamat' => $s->alamat,
                    'kecamatan' => $s->kecamatan,
                    'telepon' => $s->telepon,
                    'kepala_sekolah' => $s->profil?->kepala_sekolah,
                    'akreditasi' => $s->profil?->akreditasi,
                    'jumlah_siswa' => $s->profil?->jumlah_siswa,
                    'jumlah_guru' => $s->profil?->jumlah_guru,
                ],
            ]);

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
