<?php

namespace App\Http\Controllers;

use App\Models\Stok;
use Illuminate\Http\Request;

class StokController extends Controller
{
    public function index()
    {
        // Mengambil semua data stok beserta relasi kategori sampahnya
        $stok = Stok::with('kategori')->get();
        
        return view('stok.index', compact('stok'));
    }
}