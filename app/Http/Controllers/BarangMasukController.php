<?php

namespace App\Http\Controllers;

use App\Http\Requests\BarangMasukRequest;
use App\Models\BahanBaku;
use App\Models\BarangMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

// Mengatur pencatatan barang masuk (pembelian bahan baku).
// Hanya ada daftar dan tambah. Barang masuk tidak bisa diubah atau dihapus,
// seperti jurnal akuntansi: kesalahan dibetulkan dengan catatan baru, bukan dengan menghapus.
class BarangMasukController extends Controller
{
    // Halaman daftar barang masuk dengan penyaring bahan baku dan rentang tanggal.
    public function index(Request $request)
    {
        $filter = [
            'bahan_baku_id' => $request->query('bahan_baku_id'),
            'dari' => $request->query('dari'),
            'sampai' => $request->query('sampai'),
        ];

        $daftarBarangMasuk = BarangMasuk::query()
            ->with('bahanBaku')
            ->when($filter['bahan_baku_id'], fn ($query, $id) => $query->where('bahan_baku_id', $id))
            ->when($filter['dari'], fn ($query, $tanggal) => $query->whereDate('tanggal', '>=', $tanggal))
            ->when($filter['sampai'], fn ($query, $tanggal) => $query->whereDate('tanggal', '<=', $tanggal))
            // Terbaru di atas. Jika tanggalnya sama, yang dicatat belakangan di atas.
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('barang_masuk.index', [
            'daftarBarangMasuk' => $daftarBarangMasuk,
            'filter' => $filter,
            'daftarBahan' => BahanBaku::orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    // Formulir pencatatan barang masuk.
    public function create()
    {
        return view('barang_masuk.create', [
            'daftarBahan' => BahanBaku::orderBy('nama')->get(),
        ]);
    }

    // Menyimpan barang masuk. Tiga langkah dijalankan dalam satu transaksi basis data.
    public function store(BarangMasukRequest $request)
    {
        $data = $request->validated();

        $bahanBaku = DB::transaction(function () use ($data) {
            // lockForUpdate(): kunci baris bahan baku ini sampai transaksi selesai.
            // Jika ada dua pencatatan bahan yang sama di waktu bersamaan, yang kedua
            // menunggu yang pertama selesai, supaya stok lamanya tidak terbaca keliru.
            $bahanBaku = BahanBaku::lockForUpdate()->findOrFail($data['bahan_baku_id']);

            // 1. Hitung harga rata-rata baru (moving average cost) dari stok dan harga lama.
            $hargaRataRataBaru = $bahanBaku->hitungHargaRataRataBaru($data['jumlah'], $data['harga_beli']);

            // 2. Perbarui stok dan harga rata-rata bahan baku.
            $bahanBaku->update([
                'stok' => round($bahanBaku->stok + $data['jumlah'], 3),
                'harga_rata_rata' => $hargaRataRataBaru,
            ]);

            // 3. Simpan catatan barang masuk, termasuk harga rata-rata setelah pencatatan ini.
            $bahanBaku->barangMasuk()->create([
                'tanggal' => $data['tanggal'],
                'jumlah' => $data['jumlah'],
                'harga_beli' => $data['harga_beli'],
                'harga_rata_rata_setelah' => $hargaRataRataBaru,
            ]);

            return $bahanBaku;
        });

        return redirect('/barang-masuk')->with('berhasil',
            "Barang masuk {$bahanBaku->nama} berhasil dicatat. "
            . 'Stok sekarang ' . Number::format($bahanBaku->stok, maxPrecision: 3, locale: 'id')
            . " {$bahanBaku->satuan}, harga rata-rata "
            . Number::currency($bahanBaku->harga_rata_rata, in: 'IDR', locale: 'id') . '.');
    }
}
