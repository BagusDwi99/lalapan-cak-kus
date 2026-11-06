<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Aturan validasi formulir tambah dan ubah biaya operasional.
// Periode dikirim sebagai dua isian (bulan dan tahun), lalu disimpan
// sebagai tanggal 1 pada bulan itu, contoh: bulan 11 tahun 2026 -> 2026-11-01.
class BiayaOperasionalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Dijalankan sebelum validasi: gabungkan bulan dan tahun menjadi tanggal periode,
    // supaya bisa dipakai untuk memeriksa nama biaya yang sama dalam satu periode.
    protected function prepareForValidation(): void
    {
        if (is_numeric($this->input('bulan')) && is_numeric($this->input('tahun'))) {
            $this->merge(['periode' => $this->susunPeriode($this->input('bulan'), $this->input('tahun'))]);
        }
    }

    public function rules(): array
    {
        return [
            'nama' => [
                'required', 'string', 'max:255',
                // Nama tidak boleh sama dengan biaya lain PADA PERIODE YANG SAMA.
                // Nama yang sama di bulan lain boleh (misalnya "Listrik" setiap bulan).
                Rule::unique('biaya_operasional', 'nama')
                    ->where('periode', $this->input('periode'))
                    ->ignore($this->route('biaya_operasional')),
            ],
            'jumlah' => ['required', 'numeric', 'gt:0', 'max:999999999999'],
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'between:2000,2100'],
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
            'integer' => ':attribute tidak valid.',
            'between' => ':attribute tidak valid.',
            'nama.unique' => 'Biaya dengan nama ini sudah ada pada periode tersebut.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama' => 'Nama biaya',
            'jumlah' => 'Jumlah',
            'bulan' => 'Bulan',
            'tahun' => 'Tahun',
        ];
    }

    // Data yang disimpan ke tabel biaya_operasional.
    public function dataBiaya(): array
    {
        return [
            'nama' => $this->validated('nama'),
            'jumlah' => $this->validated('jumlah'),
            'periode' => $this->susunPeriode($this->validated('bulan'), $this->validated('tahun')),
        ];
    }

    // Contoh: bulan 3, tahun 2026 -> "2026-03-01".
    private function susunPeriode($bulan, $tahun): string
    {
        return sprintf('%04d-%02d-01', $tahun, $bulan);
    }
}
