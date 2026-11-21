<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PetaController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\ProfilSekolahController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Publik
Route::controller(PetaController::class)->group(function () {
    Route::get('/', 'index')->name('peta.index');
    Route::get('/data/sekolah', 'geojson')->name('peta.data');

    // Pencarian sekolah
    Route::get('/pencarian', 'cari')->name('peta.cari');

    // Klaster sekolah
    Route::get('/klaster', 'klaster')->name('peta.klaster');

    // Peta panas
    Route::get('/peta-panas', 'panas')->name('peta.panas');

    // Peta wilayah
    Route::get('/wilayah', 'wilayah')->name('peta.wilayah');
    Route::get('/data/wilayah', 'geojsonWilayah')
        ->name('peta.data.wilayah');

    // Rute sekolah
    Route::get('/rute', 'rute')->name('peta.rute');

    // Cek lokasi / alamat
    Route::get('/cek-alamat', 'alamat')->name('peta.alamat');
});

// Profil publik sekolah
Route::controller(ProfilSekolahController::class)->group(function () {
    Route::get('/sekolah/{sekolah}/profil-publik', 'show')
        ->name('sekolah.profil.public');
});

// Tamu
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');
});

// Pengguna yang sudah login
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');

    Route::get('/ubah-kata-sandi', [AuthController::class, 'showChangePassword'])
        ->name('password.edit');

    Route::put('/ubah-kata-sandi', [AuthController::class, 'changePassword'])
        ->name('password.update');

    // Admin
    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::resource('users', UserController::class)
                ->except('show');

            Route::patch('users/{user}/status', [UserController::class, 'toggleStatus'])
                ->name('users.toggle-status');
        });

    // Dinas
    Route::middleware('role:dinas')
        ->prefix('dinas')
        ->name('dinas.')
        ->group(function () {
            Route::resource('sekolah', SekolahController::class)
                ->only(['index', 'create', 'store', 'destroy']);
        });

    // Dinas dan Sekolah
    Route::middleware('role:dinas,sekolah')->group(function () {

        Route::get('sekolah/{sekolah}/profil',
            [ProfilSekolahController::class, 'edit'])
            ->name('sekolah.profil.edit');

        Route::put('sekolah/{sekolah}/profil',
            [ProfilSekolahController::class, 'update'])
            ->name('sekolah.profil.update');

        Route::get('sekolah/{sekolah}/edit',
            [SekolahController::class, 'edit'])
            ->name('sekolah.edit');

        Route::put('sekolah/{sekolah}',
            [SekolahController::class, 'update'])
            ->name('sekolah.update');
    });
});
