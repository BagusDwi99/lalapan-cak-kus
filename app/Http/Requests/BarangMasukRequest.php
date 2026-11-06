<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Aturan validasi formulir pencatatan barang masuk.
class BarangMasukRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bahan_baku_id' => ['required', 'integer', 'exists:bahan_baku,id'],
            // before_or_equal:today = tanggal tidak boleh di masa depan.
            // unique:tutup_buku,tanggal = tanggal yang sudah ditutup buku tidak boleh dipakai,
            // supaya catatan persediaan hari yang sudah ditutup tidak berubah lagi.
            'tanggal' => ['required', 'date', 'before_or_equal:today', Rule::unique('tutup_buku', 'tanggal')],
            'jumlah' => ['required', 'numeric', 'gt:0', 'max:9999999'],
            'harga_beli' => ['required', 'numeric', 'gt:0', 'max:9999999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'numeric' => ':attribute harus berupa angka.',
            'gt' => ':attribute harus lebih dari nol.',
            'max' => ':attribute terlalu besar.',
            'date' => ':attribute bukan tanggal yang valid.',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh di masa depan.',
            'tanggal.unique' => 'Tanggal ini sudah ditutup buku, barang masuk tidak dapat dicatat.',
            'bahan_baku_id.required' => 'Pilih bahan baku.',
            'bahan_baku_id.integer' => 'Pilihan bahan baku tidak valid.',
            'bahan_baku_id.exists' => 'Bahan baku tidak ditemukan.',
        ];
    }

    public function attributes(): array
    {
        return [
            'tanggal' => 'Tanggal',
            'jumlah' => 'Jumlah',
            'harga_beli' => 'Harga beli',
        ];
    }
}
