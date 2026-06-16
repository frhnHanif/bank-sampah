<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController; 
use App\Http\Controllers\KategoriSampahController;
use App\Http\Controllers\NasabahController;
use App\Http\Controllers\TransaksiSetorController;
use App\Http\Controllers\TabunganController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\TransaksiJualController;
use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\KonfigurasiController;
use App\Http\Controllers\AuthController;

// ─── PUBLIC ───────────────────────────────────────────────────

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index']);

// ─── AUTH PENGURUS ────────────────────────────────────────────

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── PENGURUS (harus login) ───────────────────────────────────

Route::middleware('auth')->group(function () {

    Route::resource('kategori', KategoriSampahController::class);
    Route::resource('nasabah', NasabahController::class);
    Route::resource('setor', TransaksiSetorController::class);
    Route::get('/nasabah/{id}/tabungan', [TabunganController::class, 'show'])->name('tabungan.show');
    Route::post('/nasabah/{id}/tabungan/tarik', [TabunganController::class, 'tarik'])->name('tabungan.tarik');
    Route::get('/nasabah/{id}/tabungan/pdf', [TabunganController::class, 'exportPdf'])->name('tabungan.pdf');
    Route::get('/nasabah/{id}/id-card', [App\Http\Controllers\TabunganController::class, 'generateIdCard'])->name('tabungan.idcard');
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
        Route::get('/konfigurasi', [KonfigurasiController::class, 'index'])->name('konfigurasi.index');
        Route::put('/konfigurasi/settings', [KonfigurasiController::class, 'updateSettings'])->name('konfigurasi.settings.update');
        Route::post('/konfigurasi/users', [KonfigurasiController::class, 'storeUser'])->name('konfigurasi.users.store');
        Route::put('/konfigurasi/users/{user}', [KonfigurasiController::class, 'updateUser'])->name('konfigurasi.users.update');
        Route::delete('/konfigurasi/users/{user}', [KonfigurasiController::class, 'destroyUser'])->name('konfigurasi.users.destroy');
    });

});