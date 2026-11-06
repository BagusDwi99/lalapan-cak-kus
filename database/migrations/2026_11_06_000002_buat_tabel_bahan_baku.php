<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tabel bahan baku, misalnya ayam, beras, cabai.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bahan_baku', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('satuan'); // contoh: kg, ekor, liter, ikat
            // Stok memakai decimal karena bisa pecahan, misalnya 2,5 kg.
            // Total 10 digit, 3 di antaranya di belakang koma.
            $table->decimal('stok', 10, 3)->default(0);
            $table->decimal('stok_minimum', 10, 3)->default(0);
            // Harga rata-rata per satuan (moving average cost method).
            // Dihitung ulang setiap kali ada barang masuk.
            $table->decimal('harga_rata_rata', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bahan_baku');
    }
};
