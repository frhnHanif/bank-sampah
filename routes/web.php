<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KategoriSampahController;
use App\Http\Controllers\NasabahController;
use App\Http\Controllers\TransaksiSetorController;
use App\Http\Controllers\TabunganController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('kategori', KategoriSampahController::class);
Route::resource('nasabah', NasabahController::class);
Route::resource('setor', TransaksiSetorController::class);
Route::get('/nasabah/{id}/tabungan', [TabunganController::class, 'show'])->name('tabungan.show');
Route::post('/nasabah/{id}/tabungan/tarik', [TabunganController::class, 'tarik'])->name('tabungan.tarik');