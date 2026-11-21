<?php

namespace App\Observers;

use App\Models\Sekolah;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Auth;

class SekolahObserver
{
    public function updated(Sekolah $sekolah): void
    {
        $perubahan = [];

        foreach ($sekolah->getChanges() as $kolom => $nilaiBaru) {

            if ($kolom === 'updated_at') {
                continue;
            }

            $perubahan[$kolom] = [
                'sebelum' => $sekolah->getOriginal($kolom),
                'sesudah' => $nilaiBaru,
            ];
        }

        if (empty($perubahan)) {
            return;
        }

        LogAktivitas::create([
            'user_id' => Auth::id(),
            'sekolah_id' => $sekolah->id,
            'nama_pengguna' => Auth::user()?->name ?? 'Sistem',
            'nama_sekolah' => $sekolah->nama,
            'aktivitas' => 'Mengubah data sekolah',
            'perubahan' => $perubahan,
        ]);
    }
}