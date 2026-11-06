<?php

namespace App\Http\Controllers;

use App\Http\Requests\BahanBakuRequest;
use App\Models\BahanBaku;
use Illuminate\Http\Request;

// Mengatur halaman daftar, tambah, ubah, dan hapus bahan baku.
class BahanBakuController extends Controller
{
    // Halaman daftar bahan baku, dengan pencarian dan pagination.
    public function index(Request $request)
    {
        $cari = $request->query('cari');

        $daftarBahanBaku = BahanBaku::query()
            // when(): filter nama hanya dijalankan jika kolom pencarian diisi.
            ->when($cari, fn ($query) => $query->where('nama', 'like', "%{$cari}%"))
            ->orderBy('nama')
            ->paginate(10)
            // withQueryString(): kata pencarian tetap terbawa saat pindah halaman.
            ->withQueryString();

        return view('bahan_baku.index', compact('daftarBahanBaku', 'cari'));
    }

    // Formulir tambah bahan baku.
    public function create()
    {
        // Objek kosong supaya formulir tambah dan ubah bisa memakai tampilan yang sama.
        $bahanBaku = new BahanBaku(['stok' => 0, 'stok_minimum' => 0, 'harga_rata_rata' => 0]);

        return view('bahan_baku.create', compact('bahanBaku'));
    }

    // Menyimpan bahan baku baru. Validasi sudah dijalankan oleh BahanBakuRequest.
    public function store(BahanBakuRequest $request)
    {
        // validated() hanya berisi kolom yang lolos validasi: nama, satuan, stok, stok_minimum.
        // harga_rata_rata tidak ada di sini, jadi tetap bernilai bawaan 0.
        $bahanBaku = BahanBaku::create($request->validated());

        return redirect('/bahan-baku')
            ->with('berhasil', "Bahan baku {$bahanBaku->nama} berhasil ditambahkan.");
    }

    // Formulir ubah bahan baku.
    public function edit(BahanBaku $bahanBaku)
    {
        return view('bahan_baku.edit', compact('bahanBaku'));
    }

    // Menyimpan perubahan bahan baku.
    public function update(BahanBakuRequest $request, BahanBaku $bahanBaku)
    {
        // Saat mengubah, validated() hanya berisi nama, satuan, dan stok_minimum.
        // Stok dan harga rata-rata tidak ikut berubah.
        $bahanBaku->update($request->validated());

        return redirect('/bahan-baku')
            ->with('berhasil', "Bahan baku {$bahanBaku->nama} berhasil diubah.");
    }

    // Menghapus bahan baku, kecuali jika masih dipakai di data lain.
    public function destroy(BahanBaku $bahanBaku)
    {
        // Ditolak jika masih dipakai di resep menu.
        if ($bahanBaku->menu()->exists()) {
            $namaMenu = $bahanBaku->menu()->pluck('nama')->implode(', ');

            return back()->with('gagal',
                "Bahan baku {$bahanBaku->nama} tidak dapat dihapus karena masih dipakai di resep menu: {$namaMenu}. "
                . 'Hapus dulu bahan ini dari resep menu tersebut.');
        }

        // Ditolak jika sudah punya riwayat barang masuk.
        if ($bahanBaku->barangMasuk()->exists()) {
            return back()->with('gagal',
                "Bahan baku {$bahanBaku->nama} tidak dapat dihapus karena sudah punya riwayat barang masuk. "
                . 'Riwayat ini dibutuhkan untuk perhitungan harga rata-rata dan laporan.');
        }

        // Ditolak jika sudah punya riwayat prediksi.
        if ($bahanBaku->prediksi()->exists()) {
            return back()->with('gagal',
                "Bahan baku {$bahanBaku->nama} tidak dapat dihapus karena sudah punya riwayat prediksi kebutuhan stok.");
        }

        $nama = $bahanBaku->nama;
        $bahanBaku->delete();

        return redirect('/bahan-baku')->with('berhasil', "Bahan baku {$nama} berhasil dihapus.");
    }
}
