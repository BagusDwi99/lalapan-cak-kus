<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Validasi tombol "Hitung Prediksi": bahan baku wajib dipilih dan harus ada.
class PrediksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bahan_baku_id' => ['required', 'integer', 'exists:bahan_baku,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'bahan_baku_id.required' => 'Pilih bahan baku terlebih dahulu.',
            'bahan_baku_id.integer' => 'Pilihan bahan baku tidak valid.',
            'bahan_baku_id.exists' => 'Bahan baku tidak ditemukan.',
        ];
    }
}
