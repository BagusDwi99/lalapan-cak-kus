<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Menambah kolom untuk menyimpan harga rata-rata bahan baku tepat setelah
// barang masuk ini dicatat. Nilainya seperti "foto" saat itu, sehingga riwayat
// perubahan harga rata-rata bisa dilihat kembali tanpa menghitung ulang.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang_masuk', function (Blueprint $table) {
            $table->decimal('harga_rata_rata_setelah', 12, 2)->nullable()->after('harga_beli');
        });
    }

    public function down(): void
    {
        Schema::table('barang_masuk', function (Blueprint $table) {
            $table->dropColumn('harga_rata_rata_setelah');
        });
    }
};
