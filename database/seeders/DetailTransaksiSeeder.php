<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Models\BarangMasuk;
use App\Models\DetailTransaksi;
use App\Models\Menu;
use App\Models\Transaksi;
use Illuminate\Database\Seeder;

// Mengisi rincian setiap nota, lalu menghitung HPP dan laba kotornya.
//
// Supaya HPP konsisten dengan harga beli, data diproses urut per tanggal,
// seperti yang terjadi di warung sungguhan:
//   1. Pagi hari: barang masuk dicatat -> stok bertambah, harga rata-rata dihitung ulang.
//   2. Siang hari: menu terjual -> HPP memakai harga rata-rata saat itu, stok berkurang.
// Di akhir, stok dan harga rata-rata terakhir disimpan ke tabel bahan_baku.
class DetailTransaksiSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(3);

        // Rentang porsi terjual per hari untuk setiap menu.
        // Ayam Goreng diatur khusus di bawah supaya rata-ratanya sekitar 30.
        $rentangPorsi = [
            'Lele Goreng'      => [10, 18],
            'Bebek Goreng'     => [4, 10],
            'Ikan Laut Goreng' => [3, 8],
            'Soto Ayam'        => [6, 12],
            'Bakso Sapi'       => [8, 14],
            'Nasi Goreng'      => [8, 15],
        ];

        // Ambil semua menu beserta resepnya, dikelompokkan berdasarkan nama.
        $menu = Menu::with('bahanBaku')->get()->keyBy('nama');

        // Kondisi bahan baku disimpan di array selama proses berjalan:
        // [id => ['stok' => ..., 'harga_rata_rata' => ...]]
        $kondisiBahan = [];
        foreach (BahanBaku::all() as $bahan) {
            $kondisiBahan[$bahan->id] = ['stok' => 0, 'harga_rata_rata' => 0];
        }

        $semuaTanggal = Transaksi::orderBy('tanggal')->distinct()->pluck('tanggal');

        foreach ($semuaTanggal as $tanggal) {
            // 1. Catat barang masuk hari ini.
            foreach (BarangMasuk::whereDate('tanggal', $tanggal)->get() as $masuk) {
                $id = $masuk->bahan_baku_id;

                // Harga rata-rata dihitung ulang lewat method model BahanBaku.
                // Model sementara (tidak disimpan) dibuat dari kondisi stok dan harga saat ini.
                $bahanSaatIni = new BahanBaku($kondisiBahan[$id]);
                $kondisiBahan[$id]['harga_rata_rata'] = $bahanSaatIni->hitungHargaRataRataBaru(
                    $masuk->jumlah,
                    $masuk->harga_beli
                );
                $kondisiBahan[$id]['stok'] += $masuk->jumlah;

                // Simpan "foto" harga rata-rata setelah barang masuk ini.
                $masuk->update(['harga_rata_rata_setelah' => $kondisiBahan[$id]['harga_rata_rata']]);
            }

            // 2. Hitung HPP per porsi tiap menu dengan harga rata-rata hari ini.
            //    Memakai method model Menu, dengan harga rata-rata hasil langkah 1.
            $hargaHariIni = array_map(fn ($kondisi) => $kondisi['harga_rata_rata'], $kondisiBahan);
            $hppPerPorsi = [];
            foreach ($menu as $namaMenu => $m) {
                $hppPerPorsi[$namaMenu] = $m->hitungHppPerPorsi($hargaHariIni);
            }

            // 3. Tentukan berapa porsi tiap menu terjual hari ini.
            //    Ayam Goreng: 30 ditambah dua angka acak -4..4, hasilnya 22..38
            //    dan paling sering mendekati 30.
            $porsiHariIni = ['Ayam Goreng' => 30 + mt_rand(-4, 4) + mt_rand(-4, 4)];
            foreach ($rentangPorsi as $namaMenu => [$min, $maks]) {
                $porsiHariIni[$namaMenu] = mt_rand($min, $maks);
            }

            // 4. Bagikan porsi-porsi itu ke nota hari ini secara acak.
            //    Setiap porsi dijadikan satu "kartu" berisi nama menu, lalu dikocok.
            $kartu = [];
            foreach ($porsiHariIni as $namaMenu => $porsi) {
                $kartu = array_merge($kartu, array_fill(0, $porsi, $namaMenu));
            }
            shuffle($kartu);

            $daftarNota = Transaksi::whereDate('tanggal', $tanggal)->orderBy('id')->get();
            $isiNota = array_fill(0, $daftarNota->count(), []);

            foreach ($kartu as $urutan => $namaMenu) {
                // Kartu pertama dibagi satu per nota supaya tidak ada nota kosong,
                // sisanya dibagi ke nota acak.
                $indeksNota = $urutan < $daftarNota->count()
                    ? $urutan
                    : mt_rand(0, $daftarNota->count() - 1);

                $isiNota[$indeksNota][$namaMenu] = ($isiNota[$indeksNota][$namaMenu] ?? 0) + 1;
            }

            // 5. Simpan detail transaksi, hitung total nota, dan kurangi stok.
            foreach ($daftarNota as $indeks => $nota) {
                $total = 0;

                foreach ($isiNota[$indeks] as $namaMenu => $jumlah) {
                    $m = $menu[$namaMenu];
                    $hpp = $hppPerPorsi[$namaMenu];

                    DetailTransaksi::create([
                        'transaksi_id' => $nota->id,
                        'menu_id' => $m->id,
                        'jumlah' => $jumlah,
                        'harga_jual' => $m->harga_jual,
                        'hpp' => $hpp,
                        'laba_kotor' => ($m->harga_jual - $hpp) * $jumlah,
                    ]);

                    $total += $m->harga_jual * $jumlah;

                    foreach ($m->bahanBaku as $bahan) {
                        $kondisiBahan[$bahan->id]['stok'] -= $bahan->pivot->jumlah * $jumlah;
                    }
                }

                $nota->update(['total' => $total]);
            }
        }

        // 6. Simpan stok dan harga rata-rata terakhir ke tabel bahan_baku.
        foreach ($kondisiBahan as $id => $kondisi) {
            BahanBaku::where('id', $id)->update([
                'stok' => round($kondisi['stok'], 3),
                'harga_rata_rata' => $kondisi['harga_rata_rata'],
            ]);
        }
    }
}
