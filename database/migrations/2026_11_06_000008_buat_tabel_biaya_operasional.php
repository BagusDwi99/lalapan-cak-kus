<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tabel biaya operasional bulanan, misalnya listrik, gas, gaji.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biaya_operasional', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->decimal('jumlah', 14, 2);
            // Periode bulanan, diisi tanggal 1 pada bulan tersebut (contoh: 2026-11-01).
            $table->date('periode');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biaya_operasional');
    }
};
