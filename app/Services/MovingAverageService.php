<?php

namespace App\Services;

// Rumus peramalan kebutuhan bahan baku dengan Moving Average (SMA dan WMA)
// serta pengukuran akurasinya dengan MAPE.
//
// Kesepakatan urutan data: semua array diurutkan dari periode PALING LAMA
// ke periode PALING BARU. Elemen terakhir adalah periode terbaru (misalnya kemarin).
class MovingAverageService
{
    /**
     * Simple Moving Average (SMA): rata-rata n data terakhir,
     * semua periode berbobot sama.
     *
     * Contoh: data [28, 32, 30], n = 3 -> (28 + 32 + 30) / 3 = 30
     *
     * Mengembalikan null jika n kurang dari 1 atau jumlah data kurang dari n.
     */
    public function hitungSMA(array $data, int $n): ?float
    {
        // array_values: rapikan nomor indeks menjadi 0, 1, 2, ... supaya urutannya pasti.
        $data = array_values($data);

        // n harus minimal 1 (mencegah pembagian dengan nol),
        // dan data harus cukup untuk mengisi satu jendela berisi n periode.
        if ($n < 1 || count($data) < $n) {
            return null;
        }

        // Ambil n data dari ujung belakang array (n periode terakhir).
        $jendela = array_slice($data, -$n);

        // Jumlahkan seluruh data di jendela, lalu bagi dengan n.
        return array_sum($jendela) / $n;
    }

    /**
     * Weighted Moving Average (WMA): rata-rata tertimbang n data terakhir,
     * dengan bobot terbesar untuk data paling baru.
     *
     * n diambil dari jumlah bobot. Bobot pertama untuk data PALING BARU:
     * [0.5, 0.3, 0.2] berarti 0,5 untuk periode terakhir, 0,3 untuk periode
     * sebelumnya, dan 0,2 untuk periode sebelumnya lagi.
     *
     * Contoh: data [28, 32, 30], bobot [0.5, 0.3, 0.2]
     *   -> (30 x 0,5 + 32 x 0,3 + 28 x 0,2) / (0,5 + 0,3 + 0,2) = 30,2 / 1 = 30,2
     *
     * Mengembalikan null jika bobot kosong, total bobot tidak positif,
     * atau jumlah data kurang dari jumlah bobot.
     */
    public function hitungWMA(array $data, array $bobot): ?float
    {
        $data = array_values($data);
        $bobot = array_values($bobot);

        // n = banyaknya bobot, sekaligus banyaknya data yang diambil.
        $n = count($bobot);

        // Total bobot dipakai sebagai pembagi, jadi harus lebih dari nol.
        $totalBobot = array_sum($bobot);

        // Bobot harus ada, total bobot harus positif, dan data harus cukup.
        if ($n < 1 || $totalBobot <= 0 || count($data) < $n) {
            return null;
        }

        // Ambil n data terakhir, lalu balik urutannya supaya data PALING BARU
        // berada di posisi pertama, sejajar dengan bobot pertama (bobot terbesar).
        // Contoh: [28, 32, 30] -> [30, 32, 28]
        $jendela = array_reverse(array_slice($data, -$n));

        // Kalikan setiap data dengan bobot pasangannya, lalu jumlahkan.
        $jumlahTertimbang = 0;
        for ($i = 0; $i < $n; $i++) {
            $jumlahTertimbang += $jendela[$i] * $bobot[$i];
        }

        // Bagi dengan total bobot. Jika total bobot = 1, hasilnya tidak berubah;
        // pembagian ini membuat rumus tetap benar bila bobotnya diganti (misalnya 3, 2, 1).
        return $jumlahTertimbang / $totalBobot;
    }

    /**
     * Mean Absolute Percentage Error (MAPE): rata-rata persentase kesalahan
     * prediksi terhadap nilai aktual, dalam satuan persen.
     *
     * Rumus tiap periode (APE):
     *   APE = |aktual - prediksi| / |aktual| x 100
     * MAPE = rata-rata APE dari periode yang sah.
     *
     * $aktual dan $prediksi harus berpasangan: elemen ke-i keduanya untuk periode yang sama.
     *
     * Periode dengan nilai aktual nol dilewati, karena tidak bisa dibagi nol.
     * Mengembalikan null jika array kosong, panjangnya berbeda, atau semua aktualnya nol.
     */
    public function hitungMAPE(array $aktual, array $prediksi): ?float
    {
        $aktual = array_values($aktual);
        $prediksi = array_values($prediksi);

        // Kedua array harus berisi data dan sama panjang supaya bisa dipasangkan.
        if (count($aktual) === 0 || count($aktual) !== count($prediksi)) {
            return null;
        }

        $daftarApe = [];

        for ($i = 0; $i < count($aktual); $i++) {
            // Lewati periode yang nilai aktualnya nol (pembagian dengan nol).
            if ($aktual[$i] == 0) {
                continue;
            }

            // Selisih absolut: meleset ke atas atau ke bawah sama-sama dihitung kesalahan.
            $selisih = abs($aktual[$i] - $prediksi[$i]);

            // APE periode ini dalam persen.
            $daftarApe[] = $selisih / abs($aktual[$i]) * 100;
        }

        // Jika semua aktual bernilai nol, tidak ada APE yang bisa dihitung.
        if (count($daftarApe) === 0) {
            return null;
        }

        // Rata-ratakan: dibagi banyaknya APE yang benar-benar dihitung,
        // bukan panjang array awal (periode yang dilewati tidak ikut).
        return array_sum($daftarApe) / count($daftarApe);
    }
}
