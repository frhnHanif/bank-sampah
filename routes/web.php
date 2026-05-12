<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KategoriSampahController;
use App\Http\Controllers\NasabahController;
use App\Http\Controllers\TransaksiSetorController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('kategori', KategoriSampahController::class);
Route::resource('nasabah', NasabahController::class);
Route::resource('setor', TransaksiSetorController::class);