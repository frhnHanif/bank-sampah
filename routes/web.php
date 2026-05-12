<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KategoriSampahController;
use App\Http\Controllers\NasabahController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('kategori', KategoriSampahController::class);
Route::resource('nasabah', NasabahController::class);