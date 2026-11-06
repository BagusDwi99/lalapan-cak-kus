<?php

namespace App\Http\Controllers;

use App\Http\Requests\PrediksiRequest;
use App\Models\BahanBaku;
use App\Services\PrediksiService;
use Illuminate\Http\Request;

// Halaman prediksi kebutuhan stok (khusus owner).
// Controller hanya meneruskan ke PrediksiService; tidak ada rumus di sini.
class PrediksiController extends Controller
{
    // Menampilkan penyaring dan, jika bahan baku sudah dipilih, hasil perhitungannya.
    public function index(Request $request, PrediksiService $prediksiService)
    {
        $bahanTerpilih = $request->filled('bahan_baku_id')
            ? BahanBaku::find($request->query('bahan_baku_id'))
            : null;

        return view('prediksi.index', [
            'daftarBahan' => BahanBaku::orderBy('nama')->get(['id', 'nama']),
            'bahanTerpilih' => $bahanTerpilih,
            'hasil' => $bahanTerpilih ? $prediksiService->hitung($bahanTerpilih) : null,
            'periodeN' => PrediksiService::PERIODE_N,
        ]);
    }

    // Tombol "Hitung Prediksi": hitung, simpan ke tabel prediksi, lalu tampilkan hasilnya.
    public function store(PrediksiRequest $request, PrediksiService $prediksiService)
    {
        $bahan = BahanBaku::findOrFail($request->validated('bahan_baku_id'));
        $hasil = $prediksiService->hitung($bahan);

        // Data belum cukup: tidak ada yang disimpan, halaman akan menampilkan pesannya.
        if (! $hasil['cukup']) {
            return redirect("/prediksi?bahan_baku_id={$bahan->id}");
        }

        $jumlahBaris = $prediksiService->simpan($hasil);

        // Kembali ke halaman (GET) supaya muat ulang halaman tidak mengirim formulir dua kali.
        return redirect("/prediksi?bahan_baku_id={$bahan->id}")
            ->with('berhasil', "Prediksi {$bahan->nama} berhasil dihitung dan disimpan ({$jumlahBaris} baris di tabel prediksi).");
    }
}
