<?php

namespace App\Http\Controllers;

use App\Models\BiayaOperasional;
use App\Models\DetailTransaksi;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// Laporan laba rugi sederhana per bulan (khusus owner).
//
//   Laba kotor  = total penjualan - total HPP
//   Laba bersih = laba kotor - total biaya operasional
class LabaRugiController extends Controller
{
    public function index(Request $request)
    {
        // Bulan dan tahun dari penyaring. Jika kosong atau tidak valid, pakai bulan ini.
        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);
        if ($bulan < 1 || $bulan > 12 || $tahun < 2000 || $tahun > 2100) {
            $bulan = now()->month;
            $tahun = now()->year;
        }

        // Tanggal awal dan akhir bulan, contoh: 2026-11-01 sampai 2026-11-30.
        $awalBulan = Carbon::create($tahun, $bulan, 1);
        $akhirBulan = $awalBulan->copy()->endOfMonth();
        $rentang = [$awalBulan->toDateString(), $akhirBulan->toDateString()];

        // 1. Total penjualan: jumlah kolom total dari semua transaksi bulan ini.
        $totalPenjualan = Transaksi::whereBetween('tanggal', $rentang)->sum('total');

        // 2. Total HPP: kolom hpp di detail_transaksi adalah HPP per porsi,
        //    jadi dikalikan jumlah porsi dulu baru dijumlahkan.
        $totalHpp = DetailTransaksi::whereHas('transaksi', fn ($query) => $query->whereBetween('tanggal', $rentang))
            ->sum(DB::raw('hpp * jumlah'));

        // 3. Laba kotor.
        $labaKotor = $totalPenjualan - $totalHpp;

        // 4. Biaya operasional bulan ini (periode disimpan sebagai tanggal 1).
        $daftarBiaya = BiayaOperasional::where('periode', $awalBulan->toDateString())->orderBy('nama')->get();
        $totalBiaya = $daftarBiaya->sum('jumlah');

        // 5. Laba bersih.
        $labaBersih = $labaKotor - $totalBiaya;

        return view('laba_rugi.index', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'namaPeriode' => $awalBulan->locale('id')->translatedFormat('F Y'),
            'totalPenjualan' => $totalPenjualan,
            'totalHpp' => $totalHpp,
            'labaKotor' => $labaKotor,
            'daftarBiaya' => $daftarBiaya,
            'totalBiaya' => $totalBiaya,
            'labaBersih' => $labaBersih,
        ]);
    }
}
