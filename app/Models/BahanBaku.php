<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\CarbonPeriod;
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
     * Hari tanpa pemakaian tetap muncul dengan nilai 0, supaya deret harinya tidak bolong.
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

        // SATU query untuk seluruh rentang: hanya mengembalikan tanggal yang ada pemakaiannya.
        // Hasilnya contoh: ['2026-10-07' => '34.500', '2026-10-08' => '33.000', ...]
        $pemakaianPerTanggal = DB::table('transaksi')
            ->join('detail_transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
            ->join('menu_bahan_baku', 'menu_bahan_baku.menu_id', '=', 'detail_transaksi.menu_id')
            ->where('menu_bahan_baku.bahan_baku_id', $bahanBakuId)
            ->whereBetween('transaksi.tanggal', [$awal->toDateString(), $akhir->toDateString()])
            ->groupBy('transaksi.tanggal')
            ->selectRaw('transaksi.tanggal, SUM(detail_transaksi.jumlah * menu_bahan_baku.jumlah) AS pemakaian')
            ->pluck('pemakaian', 'tanggal');

        // Susun deret lengkap dari tanggal awal sampai akhir (tanpa query lagi).
        // Tanggal yang tidak ada di hasil query berarti tidak ada pemakaian: diisi 0.
        $hasil = [];
        foreach (CarbonPeriod::create($awal, $akhir) as $hari) {
            $tanggal = $hari->toDateString();
            $hasil[$tanggal] = (float) ($pemakaianPerTanggal[$tanggal] ?? 0);
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
