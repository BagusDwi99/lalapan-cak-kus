<?php

namespace App\Http\Controllers;

use App\Services\LaporanService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

// Laporan laba rugi sederhana per bulan (khusus owner).
// Perhitungannya berada di LaporanService::labaRugi().
class LabaRugiController extends Controller
{
    public function index(Request $request, LaporanService $laporan)
    {
        // Bulan dan tahun dari penyaring. Jika kosong atau tidak valid, pakai bulan ini.
        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);
        if ($bulan < 1 || $bulan > 12 || $tahun < 2000 || $tahun > 2100) {
            $bulan = now()->month;
            $tahun = now()->year;
        }

        $awalBulan = Carbon::create($tahun, $bulan, 1);

        // totalPenjualan, totalHpp, labaKotor, daftarBiaya, totalBiaya, labaBersih
        $labaRugi = $laporan->labaRugi($awalBulan);

        return view('laba_rugi.index', $labaRugi + [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'namaPeriode' => $awalBulan->locale('id')->translatedFormat('F Y'),
        ]);
    }
}
