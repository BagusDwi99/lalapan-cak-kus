<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Models\BarangMasuk;
use App\Models\DetailTransaksi;
use App\Models\Menu;
use App\Models\Transaksi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Random\Engine\Mt19937;
use Random\Randomizer;

// Simulasi operasional harian warung: belanja pagi (barang_masuk) dan penjualan
// (detail_transaksi), lalu HPP dan laba kotor tiap item.
//
// Belanja dan penjualan disimulasikan bersama karena warung belanja HARIAN:
// jumlah yang dibeli pagi ini bergantung pada sisa stok kemarin, dan sisa stok
// bergantung pada penjualan. Data diproses urut per tanggal:
//   1. Pagi hari: belanja sampai stok mencapai batas belanja -> catat barang masuk,
//      stok bertambah, harga rata-rata dihitung ulang.
//   2. Siang hari: menu terjual -> HPP memakai harga rata-rata saat itu, stok berkurang.
// Di akhir, stok dan harga rata-rata terakhir disimpan ke tabel bahan_baku.
class DetailTransaksiSeeder extends Seeder
{
    // Pola belanja harian tiap bahan baku.
    //   batas     = stok minimal setelah belanja pagi. Harus di atas pemakaian harian terbesar
    //               supaya stok tidak pernah minus. Stok akhir kira-kira = batas - pemakaian hari terakhir.
    //   kelipatan = satuan pembelian (misalnya daging dibeli per 0,5 kg).
    //   harga     = harga beli dasar per satuan, naik-turun -5% s.d. +5% setiap belanja.
    private const POLA_BELANJA = [
        'Ayam Potong'   => ['batas' => 58,  'kelipatan' => 1,   'harga' => 9000],
        'Lele'          => ['batas' => 42,  'kelipatan' => 1,   'harga' => 3500],
        'Bebek'         => ['batas' => 15,  'kelipatan' => 1,   'harga' => 22000],
        'Ikan Laut'     => ['batas' => 17,  'kelipatan' => 1,   'harga' => 18000],
        'Daging Sapi'   => ['batas' => 3.1, 'kelipatan' => 0.5, 'harga' => 130000],
        'Beras'         => ['batas' => 31,  'kelipatan' => 1,   'harga' => 13000],
        'Minyak Goreng' => ['batas' => 10,  'kelipatan' => 1,   'harga' => 17000],
        'Sayuran'       => ['batas' => 10,  'kelipatan' => 0.5, 'harga' => 12000],
    ];

    public function run(): void
    {
        // Angka acak penjualan dibuat tetap (seed 3) supaya data selalu sama.
        mt_srand(3);

        // Angka acak harga beli memakai pembangkit TERPISAH (seed 1), supaya urutan
        // angka acak penjualan di atas tidak terganggu oleh simulasi belanja.
        $acakHarga = new Randomizer(new Mt19937(1));

        // Angka acak waktu belanja juga memakai pembangkit terpisah (seed 5),
        // supaya harga beli dan data penjualan tidak ikut berubah.
        $acakWaktu = new Randomizer(new Mt19937(5));

        $idBahan = BahanBaku::pluck('id', 'nama');

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
            // 1. Belanja pagi: isi stok tiap bahan sampai mencapai batas belanja.
            //    Waktu pencatatan tiap belanja acak antara pukul 06.00 dan 09.00, diurutkan
            //    supaya catatan yang lebih dulu juga bernomor lebih kecil.
            $daftarDetik = [];
            foreach (self::POLA_BELANJA as $pola) {
                $daftarDetik[] = $acakWaktu->getInt(6 * 3600, 9 * 3600);
            }
            sort($daftarDetik);

            foreach (self::POLA_BELANJA as $namaBahan => $pola) {
                // Ambil waktu berikutnya (dipakai berurutan, termasuk untuk bahan yang tidak dibeli).
                $waktuBelanja = Carbon::parse($tanggal)->addSeconds(array_shift($daftarDetik));
                $id = $idBahan[$namaBahan];

                // Kekurangan terhadap batas (dibulatkan 3 angka seperti kolom stok).
                $kurang = round($pola['batas'] - $kondisiBahan[$id]['stok'], 3);
                if ($kurang <= 0) {
                    continue; // stok masih cukup, tidak perlu belanja
                }

                // Dibeli dalam kelipatan satuan pembelian, dibulatkan ke atas.
                $jumlah = ceil($kurang / $pola['kelipatan']) * $pola['kelipatan'];

                // Harga beli naik-turun -5% s.d. +5%, dibulatkan ke ratusan.
                $hargaBeli = round($pola['harga'] * (1 + $acakHarga->getInt(-5, 5) / 100), -2);

                // Harga rata-rata dihitung ulang lewat method model BahanBaku.
                // Model sementara (tidak disimpan) dibuat dari kondisi stok dan harga saat ini.
                $bahanSaatIni = new BahanBaku($kondisiBahan[$id]);
                $kondisiBahan[$id]['harga_rata_rata'] = $bahanSaatIni->hitungHargaRataRataBaru($jumlah, $hargaBeli);
                $kondisiBahan[$id]['stok'] += $jumlah;

                // forceCreate supaya created_at bisa diisi waktu belanja (bukan waktu seeder dijalankan).
                BarangMasuk::forceCreate([
                    'bahan_baku_id' => $id,
                    'tanggal' => Carbon::parse($tanggal)->toDateString(),
                    'jumlah' => $jumlah,
                    'harga_beli' => $hargaBeli,
                    // "Foto" harga rata-rata setelah barang masuk ini.
                    'harga_rata_rata_setelah' => $kondisiBahan[$id]['harga_rata_rata'],
                    'created_at' => $waktuBelanja,
                    'updated_at' => $waktuBelanja,
                ]);
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

                    // Waktu pencatatan detail sama dengan waktu notanya.
                    DetailTransaksi::forceCreate([
                        'transaksi_id' => $nota->id,
                        'menu_id' => $m->id,
                        'jumlah' => $jumlah,
                        'harga_jual' => $m->harga_jual,
                        'hpp' => $hpp,
                        'laba_kotor' => ($m->harga_jual - $hpp) * $jumlah,
                        'created_at' => $nota->created_at,
                        'updated_at' => $nota->created_at,
                    ]);

                    $total += $m->harga_jual * $jumlah;

                    foreach ($m->bahanBaku as $bahan) {
                        $kondisiBahan[$bahan->id]['stok'] -= $bahan->pivot->jumlah * $jumlah;
                    }
                }

                // timestamps = false: mengisi total tanpa mengubah updated_at menjadi waktu sekarang.
                $nota->timestamps = false;
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
