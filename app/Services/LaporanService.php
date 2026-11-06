<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;

// Menyusun laporan penjualan per menu dan laporan persediaan per bahan baku
// untuk satu rentang tanggal.
class LaporanService
{
    /**
     * Laporan penjualan per menu dalam rentang tanggal.
     * Hasil: ['baris' => [...], 'total' => [...]], baris diurutkan dari penjualan terbesar.
     */
    public function penjualan(string $tanggalAwal, string $tanggalAkhir): array
    {
        // Satu query: jumlahkan detail transaksi per menu dalam rentang tanggal.
        //   penjualan = harga jual x porsi, HPP = HPP per porsi x porsi.
        $terjual = DB::table('detail_transaksi')
            ->join('transaksi', 'transaksi.id', '=', 'detail_transaksi.transaksi_id')
            ->whereBetween('transaksi.tanggal', [$tanggalAwal, $tanggalAkhir])
            ->groupBy('detail_transaksi.menu_id')
            ->selectRaw('detail_transaksi.menu_id,
                SUM(detail_transaksi.jumlah) AS porsi,
                SUM(detail_transaksi.harga_jual * detail_transaksi.jumlah) AS penjualan,
                SUM(detail_transaksi.hpp * detail_transaksi.jumlah) AS hpp,
                SUM(detail_transaksi.laba_kotor) AS laba')
            ->get()
            ->keyBy('menu_id');

        // Semua menu ikut tampil; menu yang tidak terjual bernilai 0.
        $baris = [];
        foreach (Menu::orderBy('nama')->get() as $menu) {
            $data = $terjual[$menu->id] ?? null;

            $baris[] = [
                'nama' => $menu->nama,
                'porsi' => (int) ($data->porsi ?? 0),
                'penjualan' => (float) ($data->penjualan ?? 0),
                'hpp' => (float) ($data->hpp ?? 0),
                'laba' => (float) ($data->laba ?? 0),
            ];
        }

        // Urutkan dari penjualan terbesar.
        usort($baris, fn ($a, $b) => $b['penjualan'] <=> $a['penjualan']);

        return [
            'baris' => $baris,
            'total' => [
                'porsi' => array_sum(array_column($baris, 'porsi')),
                'penjualan' => array_sum(array_column($baris, 'penjualan')),
                'hpp' => array_sum(array_column($baris, 'hpp')),
                'laba' => array_sum(array_column($baris, 'laba')),
            ],
        ];
    }

    /**
     * Laporan persediaan per bahan baku dalam rentang tanggal.
     *
     * Stok awal periode dihitung MUNDUR dari stok saat ini:
     *   stok awal = stok sekarang - barang masuk sejak tanggal awal + pemakaian sejak tanggal awal
     * Lalu:
     *   stok akhir      = stok awal + masuk dalam periode - terpakai dalam periode
     *   nilai persediaan = stok akhir x harga rata-rata saat ini
     *
     * "Sejak tanggal awal" termasuk tanggal awal itu sendiri, jadi stok awal adalah
     * stok pada pagi hari tanggal awal, sebelum ada aktivitas hari itu.
     */
    public function persediaan(string $tanggalAwal, string $tanggalAkhir): array
    {
        // Barang masuk per bahan: total sejak tanggal awal dan total di dalam periode.
        $masuk = DB::table('barang_masuk')
            ->where('tanggal', '>=', $tanggalAwal)
            ->groupBy('bahan_baku_id')
            ->selectRaw('bahan_baku_id,
                SUM(jumlah) AS sejak_awal,
                SUM(CASE WHEN tanggal <= ? THEN jumlah ELSE 0 END) AS dalam_periode', [$tanggalAkhir])
            ->get()
            ->keyBy('bahan_baku_id');

        // Pemakaian per bahan, diturunkan dari transaksi -> detail_transaksi -> menu_bahan_baku.
        // Pemakaian satu baris = porsi terjual x pemakaian bahan per porsi.
        $pakai = DB::table('transaksi')
            ->join('detail_transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
            ->join('menu_bahan_baku', 'menu_bahan_baku.menu_id', '=', 'detail_transaksi.menu_id')
            ->where('transaksi.tanggal', '>=', $tanggalAwal)
            ->groupBy('menu_bahan_baku.bahan_baku_id')
            ->selectRaw('menu_bahan_baku.bahan_baku_id,
                SUM(detail_transaksi.jumlah * menu_bahan_baku.jumlah) AS sejak_awal,
                SUM(CASE WHEN transaksi.tanggal <= ? THEN detail_transaksi.jumlah * menu_bahan_baku.jumlah ELSE 0 END) AS dalam_periode', [$tanggalAkhir])
            ->get()
            ->keyBy('bahan_baku_id');

        $baris = [];
        foreach (BahanBaku::orderBy('nama')->get() as $bahan) {
            $masukSejakAwal = (float) ($masuk[$bahan->id]->sejak_awal ?? 0);
            $masukPeriode = (float) ($masuk[$bahan->id]->dalam_periode ?? 0);
            $pakaiSejakAwal = (float) ($pakai[$bahan->id]->sejak_awal ?? 0);
            $pakaiPeriode = (float) ($pakai[$bahan->id]->dalam_periode ?? 0);

            // Hitung mundur: batalkan semua yang terjadi sejak tanggal awal.
            // Barang masuk dikurangkan kembali, pemakaian ditambahkan kembali.
            $stokAwal = (float) $bahan->stok - $masukSejakAwal + $pakaiSejakAwal;

            // Lalu maju lagi, tetapi hanya sampai tanggal akhir periode.
            $stokAkhir = $stokAwal + $masukPeriode - $pakaiPeriode;

            $hargaRataRata = (float) $bahan->harga_rata_rata;

            $baris[] = [
                'nama' => $bahan->nama,
                'satuan' => $bahan->satuan,
                'stokAwal' => $stokAwal,
                'masuk' => $masukPeriode,
                'terpakai' => $pakaiPeriode,
                'stokAkhir' => $stokAkhir,
                'stokMinimum' => (float) $bahan->stok_minimum,
                'hargaRataRata' => $hargaRataRata,
                'nilai' => $stokAkhir * $hargaRataRata,
                // Penanda: stok akhir periode kurang dari atau sama dengan stok minimum.
                'menipis' => $stokAkhir <= (float) $bahan->stok_minimum,
            ];
        }

        return [
            'baris' => $baris,
            'totalNilai' => array_sum(array_column($baris, 'nilai')),
        ];
    }
}
