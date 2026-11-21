<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';

    protected $fillable = [
        'user_id',
        'sekolah_id',
        'nama_pengguna',
        'nama_sekolah',
        'aktivitas',
        'perubahan',
    ];

    protected $casts = [
        'perubahan' => 'array',
    ];
}