<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\Menu;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;

// Mengurus penyimpanan transaksi penjualan: cek stok, hitung HPP, simpan, kurangi stok.
class TransaksiService
{
    // Laravel otomatis memberikan MovingAverageService saat TransaksiService dibuat.
    public function __construct(private MovingAverageService $movingAverage)
    {
    }

    /**
     * Menyimpan satu transaksi penjualan beserta detailnya.
     *
     * $daftarItem contoh: [['menu_id' => 1, 'jumlah' => 2], ['menu_id' => 7, 'jumlah' => 1]]
     *
     * Jika stok ada yang kurang, dilempar ValidationException: tidak ada yang tersimpan,
     * dan pengguna dikembalikan ke formulir dengan pesan bahan baku yang kurang.
     */
    public function simpan(string $tanggal, array $daftarItem, int $userId): Transaksi
    {
        // Semua langkah di dalam DB::transaction berhasil semua atau batal semua.
        return DB::transaction(function () use ($tanggal, $daftarItem, $userId) {
            // 1. Ambil menu yang dipesan beserta komposisinya.
            $idMenu = array_column($daftarItem, 'menu_id');
            $daftarMenu = Menu::with('bahanBaku')->findMany($idMenu)->keyBy('id');

            // 2. Hitung total kebutuhan tiap bahan baku untuk seluruh item.
            //    Contoh: 3 Ayam Goreng + 2 Soto Ayam -> Ayam Potong = 3 x 1 + 2 x 0,5 = 4 potong.
            $kebutuhan = []; // [bahan_baku_id => jumlah yang dibutuhkan]
            foreach ($daftarItem as $item) {
                foreach ($daftarMenu[$item['menu_id']]->bahanBaku as $bahan) {
                    $kebutuhan[$bahan->id] = ($kebutuhan[$bahan->id] ?? 0) + $bahan->pivot->jumlah * $item['jumlah'];
                }
            }
            // Dibulatkan 3 angka di belakang koma, sama seperti kolom stok,
            // supaya 0,1 x 3 tidak terbaca 0,30000000000000004.
            $kebutuhan = array_map(fn ($jumlah) => round($jumlah, 3), $kebutuhan);

            // 3. Baca bahan baku sambil dikunci (lockForUpdate).
            //    Transaksi lain yang memakai bahan yang sama harus menunggu sampai transaksi ini selesai,
            //    jadi stok dan harga rata-rata yang dibaca di sini pasti yang terbaru.
            $daftarBahan = BahanBaku::whereIn('id', array_keys($kebutuhan))->lockForUpdate()->get()->keyBy('id');

            // 4. Periksa kecukupan stok seluruh bahan. Kumpulkan semua yang kurang, baru ditolak.
            $pesanKurang = [];
            foreach ($kebutuhan as $idBahan => $jumlahDibutuhkan) {
                $bahan = $daftarBahan[$idBahan];

                if ($jumlahDibutuhkan > $bahan->stok) {
                    $pesanKurang[] = "{$bahan->nama}: dibutuhkan " . $this->angka($jumlahDibutuhkan) . " {$bahan->satuan}, "
                        . 'sisa stok ' . $this->angka($bahan->stok) . " {$bahan->satuan}.";
                }
            }

            if ($pesanKurang) {
                // Melempar exception di dalam DB::transaction = semua dibatalkan.
                throw ValidationException::withMessages(['stok' => $pesanKurang]);
            }

            // 5. Hitung HPP dan laba kotor tiap item dengan harga rata-rata terbaru.
            $hargaRataRata = $daftarBahan->pluck('harga_rata_rata', 'id')->all();
            $daftarDetail = [];
            $total = 0;

            foreach ($daftarItem as $item) {
                $menu = $daftarMenu[$item['menu_id']];
                $hppPerPorsi = $this->movingAverage->hitungHppPerPorsi($menu, $hargaRataRata);

                $daftarDetail[] = [
                    'menu_id' => $menu->id,
                    'jumlah' => $item['jumlah'],
                    'harga_jual' => $menu->harga_jual,
                    'hpp' => $hppPerPorsi,
                    // Laba kotor baris ini = (harga jual - HPP) x jumlah porsi.
                    'laba_kotor' => round(($menu->harga_jual - $hppPerPorsi) * $item['jumlah'], 2),
                ];

                $total += $menu->harga_jual * $item['jumlah'];
            }

            // 6. Simpan transaksi dan detailnya.
            $transaksi = Transaksi::create([
                'tanggal' => $tanggal,
                'total' => $total,
                'user_id' => $userId,
            ]);
            $transaksi->detailTransaksi()->createMany($daftarDetail);

            // 7. Kurangi stok tiap bahan baku. Harga rata-rata TIDAK diubah.
            foreach ($kebutuhan as $idBahan => $jumlahDibutuhkan) {
                $bahan = $daftarBahan[$idBahan];
                $bahan->update(['stok' => round($bahan->stok - $jumlahDibutuhkan, 3)]);
            }

            return $transaksi;
        });
    }

    // Format angka gaya Indonesia untuk pesan, contoh 2.5 -> "2,5".
    private function angka($nilai): string
    {
        return Number::format($nilai, maxPrecision: 3, locale: 'id');
    }
}
