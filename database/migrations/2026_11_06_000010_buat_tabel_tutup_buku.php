<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tabel rekap penutupan buku harian.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutup_buku', function (Blueprint $table) {
            $table->id();
            // Satu tanggal hanya boleh ditutup sekali.
            $table->date('tanggal')->unique();
            $table->decimal('total_penjualan', 14, 2);
            $table->decimal('total_laba', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutup_buku');
    }
};
