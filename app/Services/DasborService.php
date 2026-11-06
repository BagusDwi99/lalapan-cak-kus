<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\DetailTransaksi;
use App\Models\Transaksi;
use App\Models\TutupBuku;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

// Menyusun data ringkasan untuk halaman dasbor.
// Kasir dan owner memakai method berbeda, supaya data laba, HPP, dan biaya
// tidak pernah dihitung untuk kasir.
class DasborService
{
    // Laravel otomatis memberikan LaporanService saat DasborService dibuat.
    public function __construct(private LaporanService $laporan)
    {
    }

    /**
     * Data dasbor kasir: jumlah transaksi dan penjualan hari ini,
     * bahan baku menipis, dan status tutup buku. Tanpa laba, HPP, dan biaya.
     */
    public function dataKasir(): array
    {
        $bahanMenipis = BahanBaku::stokMenipis()->orderBy('nama')->get();

        return [
            'jumlahTransaksiHariIni' => Transaksi::whereDate('tanggal', today())->count(),
            'penjualanHariIni' => (float) Transaksi::whereDate('tanggal', today())->sum('total'),
            'bahanMenipis' => $bahanMenipis,
            'jumlahMenipis' => $bahanMenipis->count(),
            // Baris tutup buku hari ini, atau null jika belum tutup buku.
            'tutupBukuHariIni' => TutupBuku::whereDate('tanggal', today())->first(),
        ];
    }

    /**
     * Data dasbor owner: semua data kasir ditambah laba hari ini,
     * ringkasan bulan berjalan, grafik penjualan, dan menu terlaris.
     */
    public function dataOwner(): array
    {
        return $this->dataKasir() + [
            // Laba kotor hari ini = jumlah laba_kotor detail transaksi hari ini.
            'labaHariIni' => (float) DetailTransaksi::whereHas('transaksi', fn ($query) => $query->whereDate('tanggal', today()))
                ->sum('laba_kotor'),
            // Penjualan, HPP, laba kotor, biaya operasional, dan laba bersih bulan ini.
            'bulanIni' => $this->laporan->labaRugi(today()),
            'grafik' => $this->penjualanHarian(14),
            'menuTerlaris' => $this->menuTerlaris(5),
        ];
    }

    /**
     * Total penjualan per hari selama $jumlahHari terakhir, termasuk hari ini.
     * Hari tanpa transaksi bernilai 0. Hasil: ['label' => [...], 'nilai' => [...]].
     */
    private function penjualanHarian(int $jumlahHari): array
    {
        $akhir = today();
        $awal = today()->subDays($jumlahHari - 1);

        // Satu query: total penjualan per tanggal.
        $perTanggal = DB::table('transaksi')
            ->whereBetween('tanggal', [$awal->toDateString(), $akhir->toDateString()])
            ->groupBy('tanggal')
            ->selectRaw('tanggal, SUM(total) AS penjualan')
            ->pluck('penjualan', 'tanggal');

        // Susun deret tanggal lengkap; tanggal tanpa transaksi diisi 0.
        $hasil = ['label' => [], 'nilai' => []];
        foreach (CarbonPeriod::create($awal, $akhir) as $hari) {
            $hasil['label'][] = $hari->format('d/m');
            $hasil['nilai'][] = (float) ($perTanggal[$hari->toDateString()] ?? 0);
        }

        return $hasil;
    }

    /**
     * Menu terlaris bulan ini berdasarkan jumlah porsi terjual.
     * Memakai laporan penjualan bulan ini, lalu diambil $jumlah teratas yang pernah terjual.
     */
    private function menuTerlaris(int $jumlah): array
    {
        $penjualan = $this->laporan->penjualan(
            today()->startOfMonth()->toDateString(),
            today()->endOfMonth()->toDateString()
        );

        // Hanya menu yang terjual bulan ini.
        $terjual = array_filter($penjualan['baris'], fn ($baris) => $baris['porsi'] > 0);

        // Urutkan dari porsi terbanyak; jika porsinya sama, dari penjualan terbesar.
        usort($terjual, fn ($a, $b) => [$b['porsi'], $b['penjualan']] <=> [$a['porsi'], $a['penjualan']]);

        return array_slice($terjual, 0, $jumlah);
    }
}
