<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Menambah kolom peran ke tabel users bawaan Laravel.
// Peran menentukan hak akses: owner (pemilik warung) atau kasir.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('peran', ['owner', 'kasir'])->default('kasir')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('peran');
        });
    }
};
