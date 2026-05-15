<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController; // Pastikan ini di-import
use App\Http\Controllers\KategoriSampahController;
use App\Http\Controllers\NasabahController;
use App\Http\Controllers\TransaksiSetorController;
use App\Http\Controllers\TabunganController;

// 1. Ubah rute '/' agar mengarah ke Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// 2. Jika Anda ingin akses via /dashboard juga bekerja, tambahkan ini:
Route::get('/dashboard', [DashboardController::class, 'index']);

Route::resource('kategori', KategoriSampahController::class);
Route::resource('nasabah', NasabahController::class);
Route::resource('setor', TransaksiSetorController::class);
Route::get('/nasabah/{id}/tabungan', [TabunganController::class, 'show'])->name('tabungan.show');
Route::post('/nasabah/{id}/tabungan/tarik', [TabunganController::class, 'tarik'])->name('tabungan.tarik');
Route::get('/nasabah/{id}/tabungan/pdf', [TabunganController::class, 'exportPdf'])->name('tabungan.pdf');