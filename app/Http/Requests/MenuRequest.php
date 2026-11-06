<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Aturan validasi formulir tambah dan ubah menu, termasuk komposisi bahan bakunya.
//
// Komposisi dikirim dari formulir dalam bentuk array, contoh:
//   komposisi[0][bahan_baku_id] = 1, komposisi[0][jumlah] = 1
//   komposisi[1][bahan_baku_id] = 6, komposisi[1][jumlah] = 0.15
class MenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255', Rule::unique('menu', 'nama')->ignore($this->route('menu'))],
            'harga_jual' => ['required', 'numeric', 'gt:0', 'max:9999999999'],

            // Minimal satu baris komposisi.
            'komposisi' => ['required', 'array', 'min:1'],

            // Tanda * berarti aturan berlaku untuk setiap baris komposisi.
            // exists: bahan baku harus benar-benar ada di tabel bahan_baku.
            // distinct: bahan baku yang sama tidak boleh dipilih di dua baris.
            'komposisi.*.bahan_baku_id' => ['required', 'integer', 'exists:bahan_baku,id', 'distinct'],
            'komposisi.*.jumlah' => ['required', 'numeric', 'gt:0', 'max:999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'numeric' => ':attribute harus berupa angka.',
            'gt' => ':attribute harus lebih dari nol.',
            'max.numeric' => ':attribute terlalu besar.',
            'max.string' => ':attribute maksimal :max karakter.',
            'nama.unique' => 'Menu dengan nama ini sudah ada.',
            'komposisi.required' => 'Komposisi wajib diisi minimal satu bahan baku.',
            'komposisi.min' => 'Komposisi wajib diisi minimal satu bahan baku.',
            'komposisi.*.bahan_baku_id.required' => 'Pilih bahan baku.',
            'komposisi.*.bahan_baku_id.integer' => 'Pilihan bahan baku tidak valid.',
            'komposisi.*.bahan_baku_id.exists' => 'Bahan baku tidak ditemukan.',
            'komposisi.*.bahan_baku_id.distinct' => 'Bahan baku ini sudah dipilih di baris lain.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama' => 'Nama menu',
            'harga_jual' => 'Harga jual',
            'komposisi.*.jumlah' => 'Jumlah pemakaian',
        ];
    }

    // Data untuk tabel menu: hanya nama dan harga jual.
    public function dataMenu(): array
    {
        return $this->safe()->only(['nama', 'harga_jual']);
    }

    // Data untuk tabel pivot menu_bahan_baku, dalam bentuk yang dipahami sync():
    //   [bahan_baku_id => ['jumlah' => ...], ...]
    // contoh: [1 => ['jumlah' => 1], 6 => ['jumlah' => 0.15]]
    public function dataKomposisi(): array
    {
        $hasil = [];

        foreach ($this->validated('komposisi') as $baris) {
            $hasil[$baris['bahan_baku_id']] = ['jumlah' => $baris['jumlah']];
        }

        return $hasil;
    }
}
