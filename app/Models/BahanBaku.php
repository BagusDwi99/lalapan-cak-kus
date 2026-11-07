<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// Model untuk tabel bahan_baku.
class BahanBaku extends Model
{
    // Nama tabel ditulis manual karena Laravel akan menebak "bahan_bakus" (bahasa Inggris).
    protected $table = 'bahan_baku';

    // Kolom yang boleh diisi lewat create() atau update().
    protected $fillable = [
        'nama',
        'satuan',
        'stok',
        'stok_minimum',
        'harga_rata_rata',
    ];

    // Mengubah tipe data saat dibaca dari basis data.
    // decimal:3 artinya angka dengan 3 digit di belakang koma.
    protected $casts = [
        'stok' => 'decimal:3',
        'stok_minimum' => 'decimal:3',
        'harga_rata_rata' => 'decimal:2',
    ];

    // Scope: hanya bahan baku yang stoknya kurang dari atau sama dengan stok minimum.
    // Cara pakai: BahanBaku::stokMenipis()->get() atau BahanBaku::stokMenipis()->count()
    public function scopeStokMenipis(Builder $query): void
    {
        // whereColumn membandingkan dua kolom di baris yang sama.
        $query->whereColumn('stok', '<=', 'stok_minimum');
    }

    /**
     * Pemakaian bahan baku per hari, diturunkan dari transaksi penjualan
     * (transaksi -> detail_transaksi -> menu_bahan_baku). Data ini menjadi masukan peramalan.
     *
     * Pemakaian satu hari = jumlah dari (porsi terjual x pemakaian bahan per porsi)
     * untuk seluruh menu yang terjual hari itu.
     *
     * Hasil: array [tanggal => pemakaian], urut dari tanggal terlama ke terbaru,
     * contoh ['2026-10-07' => 34.5, '2026-10-08' => 33, ...].
     *
     * Dua keadaan dibedakan:
     *   - Tanggal yang ADA transaksi, tetapi tidak ada menu berbahan ini yang terjual
     *     -> muncul dengan nilai 0, karena memang pemakaiannya nol.
     *   - Tanggal yang TIDAK ADA transaksi sama sekali (warung tutup atau belum dicatat)
     *     -> tidak dimasukkan ke deret, karena tidak ada data, bukan pemakaian nol.
     */
    public static function getPemakaianHarian(int $bahanBakuId, $tanggalAwal, $tanggalAkhir): array
    {
        // Ubah masukan (teks atau objek tanggal) menjadi objek tanggal tanpa jam.
        $awal = Carbon::parse($tanggalAwal)->startOfDay();
        $akhir = Carbon::parse($tanggalAkhir)->startOfDay();

        // Rentang terbalik (awal setelah akhir) tidak punya hari sama sekali.
        if ($awal->gt($akhir)) {
            return [];
        }

        // SATU query untuk seluruh rentang, berangkat dari tabel transaksi:
        // setiap tanggal yang punya transaksi pasti muncul tepat satu kali.
        //
        // LEFT JOIN (bukan JOIN) dan syarat bahan baku diletakkan DI DALAM join,
        // bukan di WHERE. Akibatnya tanggal yang transaksinya tidak memakai bahan ini
        // tidak hilang: kolom menu_bahan_baku-nya kosong (NULL), SUM-nya kosong,
        // lalu COALESCE mengubahnya menjadi 0.
        $pemakaianPerTanggal = DB::table('transaksi')
            ->leftJoin('detail_transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
            ->leftJoin('menu_bahan_baku', function ($join) use ($bahanBakuId) {
                $join->on('menu_bahan_baku.menu_id', '=', 'detail_transaksi.menu_id')
                    ->where('menu_bahan_baku.bahan_baku_id', '=', $bahanBakuId);
            })
            ->whereBetween('transaksi.tanggal', [$awal->toDateString(), $akhir->toDateString()])
            ->groupBy('transaksi.tanggal')
            ->orderBy('transaksi.tanggal')
            ->selectRaw('transaksi.tanggal, COALESCE(SUM(detail_transaksi.jumlah * menu_bahan_baku.jumlah), 0) AS pemakaian')
            ->pluck('pemakaian', 'tanggal');

        // Ubah nilai teks dari basis data menjadi angka. Urutan tanggal sudah dari query.
        $hasil = [];
        foreach ($pemakaianPerTanggal as $tanggal => $pemakaian) {
            $hasil[$tanggal] = (float) $pemakaian;
        }

        return $hasil;
    }

    /**
     * Moving average cost method: menghitung harga rata-rata baru bahan ini
     * jika menerima barang masuk. Dipanggil saat pencatatan barang masuk.
     *
     * Rumus:
     *   harga rata-rata baru = (stok lama x harga rata-rata lama + jumlah masuk x harga beli)
     *                          / (stok lama + jumlah masuk)
     *
     * Contoh: stok 10 kg harga rata-rata 12.000, lalu masuk 10 kg harga 14.000.
     *   (10 x 12.000 + 10 x 14.000) / 20 = 13.000
     */
    public function hitungHargaRataRataBaru(float $jumlahMasuk, float $hargaBeli): float
    {
        $stokLama = (float) $this->stok;
        $hargaRataRataLama = (float) $this->harga_rata_rata;

        // Jika stok lama kosong, harga rata-rata baru sama dengan harga beli.
        if ($stokLama <= 0) {
            return round($hargaBeli, 2);
        }

        // Nilai uang stok lama dan nilai uang barang yang baru masuk.
        $nilaiStokLama = $stokLama * $hargaRataRataLama;
        $nilaiBarangMasuk = $jumlahMasuk * $hargaBeli;

        // Total nilai dibagi total stok setelah barang masuk.
        return round(($nilaiStokLama + $nilaiBarangMasuk) / ($stokLama + $jumlahMasuk), 2);
    }

    // Satu bahan baku dipakai di banyak menu (lewat tabel resep menu_bahan_baku).
    public function menu(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'menu_bahan_baku')
            ->using(MenuBahanBaku::class)
            ->withPivot('jumlah')
            ->withTimestamps();
    }

    // Satu bahan baku punya banyak catatan barang masuk.
    public function barangMasuk(): HasMany
    {
        return $this->hasMany(BarangMasuk::class);
    }

    // Satu bahan baku punya banyak hasil prediksi.
    public function prediksi(): HasMany
    {
        return $this->hasMany(Prediksi::class);
    }
}
