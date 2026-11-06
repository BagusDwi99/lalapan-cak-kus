<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Aturan validasi formulir transaksi penjualan.
//
// Item dikirim dalam bentuk array, contoh:
//   item[0][menu_id] = 1, item[0][jumlah] = 2
//   item[1][menu_id] = 7, item[1][jumlah] = 1
class TransaksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // unique:tutup_buku,tanggal = tanggal ini TIDAK boleh ada di tabel tutup_buku.
            // Jika hari itu sudah ditutup buku, transaksi baru pada tanggal itu ditolak,
            // supaya rekap di tutup_buku tetap sama dengan transaksi yang tercatat.
            'tanggal' => ['required', 'date', 'before_or_equal:today', Rule::unique('tutup_buku', 'tanggal')],
            'item' => ['required', 'array', 'min:1'],
            // distinct: menu yang sama tidak boleh muncul di dua baris.
            'item.*.menu_id' => ['required', 'integer', 'exists:menu,id', 'distinct'],
            // integer: jumlah porsi harus bilangan bulat, min:1 = lebih dari nol.
            'item.*.jumlah' => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Tanggal tidak valid.',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh di masa depan.',
            'tanggal.unique' => 'Tanggal ini sudah ditutup buku, transaksi tidak dapat dicatat.',
            'item.required' => 'Transaksi wajib berisi minimal satu item.',
            'item.min' => 'Transaksi wajib berisi minimal satu item.',
            'item.*.menu_id.required' => 'Pilih menu.',
            'item.*.menu_id.integer' => 'Pilihan menu tidak valid.',
            'item.*.menu_id.exists' => 'Menu tidak ditemukan.',
            'item.*.menu_id.distinct' => 'Menu ini sudah ada di baris lain. Tambahkan jumlah porsinya saja.',
            'item.*.jumlah.required' => 'Jumlah porsi wajib diisi.',
            'item.*.jumlah.integer' => 'Jumlah porsi harus bilangan bulat.',
            'item.*.jumlah.min' => 'Jumlah porsi harus lebih dari nol.',
            'item.*.jumlah.max' => 'Jumlah porsi terlalu besar.',
        ];
    }
}
