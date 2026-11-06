<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Aturan validasi formulir tambah dan ubah bahan baku.
// Laravel menjalankan validasi ini otomatis sebelum method controller dipanggil.
// Jika gagal, pengguna dikembalikan ke formulir beserta pesan kesalahannya.
class BahanBakuRequest extends FormRequest
{
    // Semua pengguna yang sudah login boleh mengirim formulir ini.
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Saat mengubah, ambil data bahan baku yang sedang diubah dari alamat URL.
        // Saat menambah, nilainya null.
        $bahanBaku = $this->route('bahan_baku');

        $aturan = [
            // unique: nama tidak boleh sama dengan bahan baku lain.
            // ignore(): saat mengubah, nama milik data itu sendiri tidak dihitung sebagai duplikat.
            'nama' => ['required', 'string', 'max:255', Rule::unique('bahan_baku', 'nama')->ignore($bahanBaku)],
            'satuan' => ['required', 'string', 'max:50'],
            'stok_minimum' => ['required', 'numeric', 'min:0', 'max:9999999'],
        ];

        // Stok awal hanya divalidasi (dan hanya ikut tersimpan) saat menambah data baru.
        // Saat mengubah, stok tidak ada di daftar aturan, jadi tidak akan ikut tersimpan
        // walaupun ada yang sengaja mengirimkannya.
        if ($this->isMethod('post')) {
            $aturan['stok'] = ['required', 'numeric', 'min:0', 'max:9999999'];
        }

        return $aturan;
    }

    // Pesan kesalahan dalam bahasa Indonesia.
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'numeric' => ':attribute harus berupa angka.',
            'min' => ':attribute tidak boleh negatif.',
            'max.numeric' => ':attribute terlalu besar.',
            'max.string' => ':attribute maksimal :max karakter.',
            'nama.unique' => 'Bahan baku dengan nama ini sudah ada.',
        ];
    }

    // Nama kolom yang ditampilkan di pesan kesalahan.
    public function attributes(): array
    {
        return [
            'nama' => 'Nama',
            'satuan' => 'Satuan',
            'stok' => 'Stok awal',
            'stok_minimum' => 'Stok minimum',
        ];
    }
}
