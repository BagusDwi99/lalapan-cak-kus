<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;

// Halaman dasbor.
class DasborController extends Controller
{
    public function index()
    {
        // Jumlah bahan baku yang stoknya kurang dari atau sama dengan stok minimum.
        $jumlahStokMenipis = BahanBaku::stokMenipis()->count();

        return view('dasbor', compact('jumlahStokMenipis'));
    }
}
