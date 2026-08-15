<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JenisSampahController;
use App\Http\Controllers\KelompokMaterialController;
use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\KonfigurasiController;
use App\Http\Controllers\NasabahAuthController;
use App\Http\Controllers\NasabahController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\TabunganController;
use App\Http\Controllers\TransaksiJualController;
use App\Http\Controllers\TransaksiSetorController;
use Illuminate\Support\Facades\Route;

// ─── PUBLIC ───────────────────────────────────────────────────

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index']);

// ─── AUTH PENGURUS ────────────────────────────────────────────

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── CEK REKENING NASABAH ────────────────────────────────────

Route::get('/nasabah/login', [NasabahAuthController::class, 'showLogin'])->name('nasabah.login');
Route::post('/cek-rekening', [NasabahAuthController::class, 'cek'])->name('nasabah.cek');
Route::post('/nasabah/logout', [NasabahAuthController::class, 'logout'])->name('nasabah.logout');

// ─── TABUNGAN (pengurus login ATAU nasabah session) ──────────

Route::middleware('nasabah.access')->group(function () {
    Route::get('/nasabah/{id}/tabungan', [TabunganController::class, 'show'])->name('tabungan.show');
    Route::get('/nasabah/{id}/tabungan/pdf', [TabunganController::class, 'exportPdf'])->name('tabungan.pdf');
    Route::get('/nasabah/{id}/id-card', [TabunganController::class, 'generateIdCard'])->name('tabungan.idcard');
});

// ─── PENGURUS (harus login) ───────────────────────────────────

Route::middleware('auth')->group(function () {

    Route::resource('jenis-sampah', JenisSampahController::class)->parameters(['jenis-sampah' => 'jenisSampah'])->only(['index', 'store', 'update', 'destroy']);
    Route::resource('nasabah', NasabahController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('setor', TransaksiSetorController::class)->only(['index', 'create', 'store']);
    Route::post('/nasabah/{id}/tabungan/tarik', [TabunganController::class, 'tarik'])->name('tabungan.tarik');
    Route::get('/stok', [StokController::class, 'index'])->name('stok.index');
    Route::get('/jual', [TransaksiJualController::class, 'create'])->name('jual.create');
    Route::post('/jual', [TransaksiJualController::class, 'store'])->name('jual.store');
    Route::get('/keuangan', [KeuanganController::class, 'index'])->name('keuangan.index');
    Route::post('/keuangan/operasional', [KeuanganController::class, 'storeOperasional'])->name('keuangan.operasional');
    Route::get('/keuangan/pdf', [KeuanganController::class, 'exportPdf'])->name('keuangan.pdf');

    // ─── ADMIN: PIN GATE ───────────────────────────────────────
    Route::get('/konfigurasi/pin', [KonfigurasiController::class, 'showPin'])->name('konfigurasi.pin');
    Route::post('/konfigurasi/pin', [KonfigurasiController::class, 'verifyPin'])->name('konfigurasi.pin.verify');
    Route::get('/konfigurasi/pin/logout', [KonfigurasiController::class, 'logoutPin'])->name('konfigurasi.pin.logout');

    Route::middleware('admin.pin')->group(function () {
        Route::get('/kelompok-material', [KelompokMaterialController::class, 'index'])->name('kelompok-material.index');
        Route::post('/kelompok-material', [KelompokMaterialController::class, 'store'])->name('kelompok-material.store');
        Route::put('/kelompok-material/{kelompokMaterial}', [KelompokMaterialController::class, 'update'])->name('kelompok-material.update');
        Route::patch('/kelompok-material/{kelompokMaterial}/toggle', [KelompokMaterialController::class, 'toggle'])->name('kelompok-material.toggle');
        Route::get('/konfigurasi', [KonfigurasiController::class, 'index'])->name('konfigurasi.index');
        Route::put('/konfigurasi/settings', [KonfigurasiController::class, 'updateSettings'])->name('konfigurasi.settings.update');
        Route::post('/konfigurasi/users', [KonfigurasiController::class, 'storeUser'])->name('konfigurasi.users.store');
        Route::put('/konfigurasi/users/{user}', [KonfigurasiController::class, 'updateUser'])->name('konfigurasi.users.update');
        Route::delete('/konfigurasi/users/{user}', [KonfigurasiController::class, 'destroyUser'])->name('konfigurasi.users.destroy');
    });

});
