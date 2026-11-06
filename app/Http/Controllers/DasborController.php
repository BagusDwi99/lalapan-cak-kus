<?php

namespace App\Http\Controllers;

use App\Services\DasborService;
use Illuminate\Http\Request;

// Halaman dasbor. Isinya berbeda untuk owner dan kasir.
// Perhitungannya berada di DasborService.
class DasborController extends Controller
{
    public function index(Request $request, DasborService $dasbor)
    {
        // Owner melihat laba, ringkasan bulanan, grafik, dan menu terlaris.
        // Kasir hanya melihat data operasional hari ini; data laba tidak dihitung sama sekali.
        $tampilkanLaba = $request->user()->bolehLihatLaba();

        $data = $tampilkanLaba ? $dasbor->dataOwner() : $dasbor->dataKasir();

        return view('dasbor', $data + ['tampilkanLaba' => $tampilkanLaba]);
    }
}
