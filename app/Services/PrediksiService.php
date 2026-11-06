<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\Prediksi;
use App\Models\Transaksi;
use App\Models\TutupBuku;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// Menyusun prediksi kebutuhan bahan baku: menyiapkan data, menggeser jendela,
// memilih varian terbaik, dan menyimpan hasil ke tabel prediksi.
//
// Semua angka SMA, WMA, APE, dan MAPE dihitung oleh MovingAverageService.
// Kelas ini hanya mengatur urutan langkah dan menyusun hasilnya.
class PrediksiService
{
    // Parameter peramalan yang sudah final (lihat CLAUDE.md).
    public const PERIODE_N = 3;
    public const BOBOT_WMA = [0.5, 0.3, 0.2]; // bobot pertama untuk periode paling baru
    public const JUMLAH_HARI = 30;

    // Laravel otomatis memberikan MovingAverageService saat PrediksiService dibuat.
    public function __construct(private MovingAverageService $movingAverage)
    {
    }

    /**
     * Menghitung seluruh hasil prediksi untuk satu bahan baku (tanpa menyimpan).
     * Hasilnya array berisi deret per hari, MAPE, prediksi berikutnya, rekomendasi, dan data grafik.
     */
    public function hitung(BahanBaku $bahan): array
    {
        // 1. Tentukan rentang data.
        //    Data hari ini baru lengkap setelah tutup buku. Jika hari ini sudah ditutup buku,
        //    deret berakhir hari ini dan prediksinya untuk besok. Jika belum, deret berakhir
        //    kemarin dan prediksinya untuk hari ini.
        $akhir = TutupBuku::whereDate('tanggal', today())->exists() ? today() : today()->subDay();
        $awal = $akhir->copy()->subDays(self::JUMLAH_HARI - 1);

        // Hari sebelum transaksi pertama di sistem bukan "pemakaian nol",
        // melainkan "sistem belum dipakai", jadi tidak ikut dihitung.
        $transaksiPertama = Transaksi::min('tanggal');
        if ($transaksiPertama !== null && Carbon::parse($transaksiPertama)->gt($awal)) {
            $awal = Carbon::parse($transaksiPertama);
        }

        // 2. Ambil pemakaian harian dari transaksi penjualan.
        $pemakaian = $transaksiPertama === null
            ? []
            : BahanBaku::getPemakaianHarian($bahan->id, $awal, $akhir);

        $tanggalTarget = $akhir->copy()->addDay();

        $hasil = [
            'bahan' => $bahan,
            'jumlahHari' => count($pemakaian),
            // Minimal 4 hari: 3 hari untuk jendela + 1 hari pembanding untuk MAPE.
            'minimalHari' => self::PERIODE_N + 1,
            'tanggalTarget' => $tanggalTarget,
            'targetBesok' => $tanggalTarget->isTomorrow(),
        ];

        $hasil['cukup'] = $hasil['jumlahHari'] >= $hasil['minimalHari'];
        if (! $hasil['cukup']) {
            return $hasil;
        }

        $daftarTanggal = array_keys($pemakaian);
        $aktual = array_values($pemakaian);

        // 3. Jendela bergeser: prediksi untuk hari ke-i hanya memakai data SEBELUM hari ke-i.
        //    Prediksi pertama untuk hari ke-4 (indeks 3), dari hari ke-1 sampai ke-3.
        $baris = [];
        for ($i = self::PERIODE_N; $i < count($aktual); $i++) {
            $dataSebelumnya = array_slice($aktual, 0, $i);

            $prediksiSma = $this->movingAverage->hitungSMA($dataSebelumnya, self::PERIODE_N);
            $prediksiWma = $this->movingAverage->hitungWMA($dataSebelumnya, self::BOBOT_WMA);

            $baris[] = [
                'tanggal' => $daftarTanggal[$i],
                'aktual' => $aktual[$i],
                'sma' => $prediksiSma,
                // APE satu hari = MAPE dari satu pasangan data.
                // Hasilnya null jika aktual hari itu nol (tidak bisa dibagi nol).
                'apeSma' => $this->movingAverage->hitungMAPE([$aktual[$i]], [$prediksiSma]),
                'wma' => $prediksiWma,
                'apeWma' => $this->movingAverage->hitungMAPE([$aktual[$i]], [$prediksiWma]),
            ];
        }
        $hasil['baris'] = $baris;

        // 4. MAPE kedua varian, dari seluruh hari yang punya prediksi.
        $deretAktual = array_column($baris, 'aktual');
        $hasil['mapeSma'] = $this->movingAverage->hitungMAPE($deretAktual, array_column($baris, 'sma'));
        $hasil['mapeWma'] = $this->movingAverage->hitungMAPE($deretAktual, array_column($baris, 'wma'));

        // 5. Prediksi untuk periode berikutnya dari 3 hari terakhir.
        $hasil['prediksiSma'] = $this->movingAverage->hitungSMA($aktual, self::PERIODE_N);
        $hasil['prediksiWma'] = $this->movingAverage->hitungWMA($aktual, self::BOBOT_WMA);

        // 6. Varian terpilih = MAPE terkecil.
        $hasil['metodeTerpilih'] = $this->pilihMetode($hasil['mapeSma'], $hasil['mapeWma']);
        // Jika MAPE tidak bisa dihitung (semua aktual nol), prediksi tetap memakai SMA.
        $hasil['metodeDipakai'] = $hasil['metodeTerpilih'] ?? 'SMA';
        $hasil['prediksiTerpilih'] = $hasil['metodeDipakai'] === 'WMA' ? $hasil['prediksiWma'] : $hasil['prediksiSma'];
        $hasil['mapeTerpilih'] = $hasil['metodeDipakai'] === 'WMA' ? $hasil['mapeWma'] : $hasil['mapeSma'];
        $hasil['kategori'] = $this->kategoriLewis($hasil['mapeTerpilih']);

        // 7. Rekomendasi pembelian = prediksi - stok tersedia, minimal nol.
        $hasil['stok'] = (float) $bahan->stok;
        $hasil['selisih'] = $hasil['prediksiTerpilih'] - $hasil['stok'];
        $hasil['rekomendasi'] = max(0, $hasil['selisih']);

        // 8. Data grafik: seluruh hari + satu titik prediksi berikutnya.
        //    Prediksi kosong (null) untuk 3 hari pertama karena jendelanya belum penuh.
        $kosong = array_fill(0, self::PERIODE_N, null);
        $hasil['grafik'] = [
            'label' => array_merge(
                array_map(fn ($t) => Carbon::parse($t)->format('d/m'), $daftarTanggal),
                [$tanggalTarget->format('d/m')]
            ),
            'aktual' => array_merge($aktual, [null]),
            'sma' => array_merge($kosong, array_column($baris, 'sma'), [$hasil['prediksiSma']]),
            'wma' => array_merge($kosong, array_column($baris, 'wma'), [$hasil['prediksiWma']]),
        ];

        return $hasil;
    }

    /**
     * Menyimpan hasil ke tabel prediksi: satu baris per varian per tanggal,
     * termasuk prediksi periode berikutnya (nilai aktual dan APE masih kosong).
     * Jika tombol ditekan lagi, baris yang sama diperbarui, bukan digandakan.
     * Mengembalikan jumlah baris yang disimpan.
     */
    public function simpan(array $hasil): int
    {
        $bahanId = $hasil['bahan']->id;
        $jumlah = 0;

        DB::transaction(function () use ($hasil, $bahanId, &$jumlah) {
            foreach ($hasil['baris'] as $b) {
                $this->simpanSatu($bahanId, $b['tanggal'], 'SMA', $b['sma'], $b['aktual'], $b['apeSma']);
                $this->simpanSatu($bahanId, $b['tanggal'], 'WMA', $b['wma'], $b['aktual'], $b['apeWma']);
                $jumlah += 2;
            }

            $target = $hasil['tanggalTarget']->toDateString();
            $this->simpanSatu($bahanId, $target, 'SMA', $hasil['prediksiSma'], null, null);
            $this->simpanSatu($bahanId, $target, 'WMA', $hasil['prediksiWma'], null, null);
            $jumlah += 2;
        });

        return $jumlah;
    }

    /**
     * Kategori akurasi menurut kriteria Lewis (istilah persis sesuai CLAUDE.md).
     */
    public function kategoriLewis(?float $mape): ?string
    {
        if ($mape === null) {
            return null;
        }

        if ($mape < 10) {
            return 'Sangat akurat';
        }
        if ($mape < 20) {
            return 'Akurat';
        }
        if ($mape < 50) {
            return 'Wajar';
        }

        return 'Tidak akurat';
    }

    // Varian dengan MAPE lebih kecil. Jika sama persis, dipilih SMA karena lebih sederhana.
    private function pilihMetode(?float $mapeSma, ?float $mapeWma): ?string
    {
        if ($mapeSma === null && $mapeWma === null) {
            return null;
        }
        if ($mapeWma === null) {
            return 'SMA';
        }
        if ($mapeSma === null) {
            return 'WMA';
        }

        return $mapeWma < $mapeSma ? 'WMA' : 'SMA';
    }

    // Simpan atau perbarui satu baris prediksi. Pembulatan dilakukan di sini, saat menyimpan.
    private function simpanSatu(int $bahanId, string $tanggal, string $metode, float $prediksi, ?float $aktual, ?float $ape): void
    {
        Prediksi::updateOrCreate(
            [
                'bahan_baku_id' => $bahanId,
                'tanggal' => $tanggal,
                'metode' => $metode,
                'periode_n' => self::PERIODE_N,
            ],
            [
                'nilai_prediksi' => round($prediksi, 3),
                'nilai_aktual' => $aktual === null ? null : round($aktual, 3),
                'ape' => $ape === null ? null : round($ape, 2),
            ]
        );
    }
}
