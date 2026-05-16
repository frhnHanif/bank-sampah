<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController; 
use App\Http\Controllers\KategoriSampahController;
use App\Http\Controllers\NasabahController;
use App\Http\Controllers\TransaksiSetorController;
use App\Http\Controllers\TabunganController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\TransaksiJualController;

// Route Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index']);

// Route Kategori Sampah
Route::resource('kategori', KategoriSampahController::class);

// Route Nasabah
Route::resource('nasabah', NasabahController::class);

// Route Transaksi Setor
Route::resource('setor', TransaksiSetorController::class);

// Route Tabungan
Route::get('/nasabah/{id}/tabungan', [TabunganController::class, 'show'])->name('tabungan.show');
Route::post('/nasabah/{id}/tabungan/tarik', [TabunganController::class, 'tarik'])->name('tabungan.tarik');
Route::get('/nasabah/{id}/tabungan/pdf', [TabunganController::class, 'exportPdf'])->name('tabungan.pdf');
Route::get('/nasabah/{id}/id-card', [App\Http\Controllers\TabunganController::class, 'generateIdCard'])->name('tabungan.idcard');

// Route Stok
Route::get('/stok', [StokController::class, 'index'])->name('stok.index');

// Route Transaksi Jual
Route::get('/jual', [TransaksiJualController::class, 'create'])->name('jual.create');
Route::post('/jual', [TransaksiJualController::class, 'store'])->name('jual.store');