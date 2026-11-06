<?php

namespace App\Http\Controllers;

use App\Services\LaporanService;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

// Halaman laporan penjualan dan laporan persediaan (khusus owner).
// Perhitungannya berada di LaporanService.
class LaporanController extends Controller
{
    public function index(Request $request, LaporanService $laporan)
    {
        // Setiap tab punya rentang tanggal sendiri. Bawaannya bulan berjalan.
        [$jualDari, $jualSampai] = $this->rentang($request->query('jual_dari'), $request->query('jual_sampai'));
        [$stokDari, $stokSampai] = $this->rentang($request->query('stok_dari'), $request->query('stok_sampai'));

        // Tab yang dibuka: penjualan (bawaan) atau persediaan.
        $tab = $request->query('tab') === 'persediaan' ? 'persediaan' : 'penjualan';

        return view('laporan.index', [
            'tab' => $tab,
            'jualDari' => $jualDari,
            'jualSampai' => $jualSampai,
            'stokDari' => $stokDari,
            'stokSampai' => $stokSampai,
            'penjualan' => $laporan->penjualan($jualDari->toDateString(), $jualSampai->toDateString()),
            'persediaan' => $laporan->persediaan($stokDari->toDateString(), $stokSampai->toDateString()),
        ]);
    }

    // Membaca rentang tanggal dari penyaring.
    // Kosong atau tidak valid -> bulan berjalan. Jika terbalik (awal setelah akhir), ditukar.
    private function rentang(?string $dari, ?string $sampai): array
    {
        $awal = $this->bacaTanggal($dari) ?? now()->startOfMonth()->startOfDay();
        $akhir = $this->bacaTanggal($sampai) ?? now()->endOfMonth()->startOfDay();

        if ($awal->gt($akhir)) {
            [$awal, $akhir] = [$akhir, $awal];
        }

        return [$awal, $akhir];
    }

    // Mengubah teks "2026-11-01" menjadi tanggal. Mengembalikan null jika formatnya salah
    // atau tanggalnya tidak ada (misalnya 2026-02-30).
    private function bacaTanggal(?string $teks): ?Carbon
    {
        if (! $teks) {
            return null;
        }

        $tanggal = DateTime::createFromFormat('!Y-m-d', $teks);

        // Dicocokkan ulang supaya tanggal yang "meluap" (2026-02-30 -> 2026-03-02) ditolak.
        if ($tanggal === false || $tanggal->format('Y-m-d') !== $teks) {
            return null;
        }

        return Carbon::instance($tanggal);
    }
}
