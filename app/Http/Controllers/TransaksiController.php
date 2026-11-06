<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransaksiRequest;
use App\Models\Menu;
use App\Models\Transaksi;
use App\Services\TransaksiService;
use Illuminate\Http\Request;

// Mengatur halaman transaksi penjualan.
// Hanya ada daftar, detail, dan tambah. Transaksi tidak bisa diubah atau dihapus.
class TransaksiController extends Controller
{
    // Halaman daftar transaksi dengan penyaring rentang tanggal.
    public function index(Request $request)
    {
        $filter = [
            'dari' => $request->query('dari'),
            'sampai' => $request->query('sampai'),
        ];

        // HPP dan laba kotor hanya untuk owner.
        $tampilkanLaba = $this->bolehLihatLaba($request);

        $daftarTransaksi = Transaksi::query()
            ->with('user')
            // withSum: jumlahkan kolom di detail_transaksi untuk setiap transaksi.
            // Hasilnya tersedia sebagai $transaksi->jumlah_porsi dan $transaksi->total_laba.
            ->withSum('detailTransaksi as jumlah_porsi', 'jumlah')
            // Total laba hanya diambil dari basis data jika pengguna boleh melihatnya.
            ->when($tampilkanLaba, fn ($query) => $query->withSum('detailTransaksi as total_laba', 'laba_kotor'))
            ->when($filter['dari'], fn ($query, $tanggal) => $query->whereDate('tanggal', '>=', $tanggal))
            ->when($filter['sampai'], fn ($query, $tanggal) => $query->whereDate('tanggal', '<=', $tanggal))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('transaksi.index', compact('daftarTransaksi', 'filter', 'tampilkanLaba'));
    }

    // Formulir transaksi baru.
    public function create()
    {
        return view('transaksi.create', [
            'daftarMenu' => Menu::orderBy('nama')->get(['id', 'nama', 'harga_jual']),
            // Jika validasi gagal, tampilkan lagi baris yang tadi diisi. Jika tidak, satu baris kosong.
            'daftarItem' => old('item', [['menu_id' => null, 'jumlah' => 1]]),
        ]);
    }

    // Menyimpan transaksi. Seluruh prosesnya ada di TransaksiService.
    public function store(TransaksiRequest $request, TransaksiService $transaksiService)
    {
        $transaksi = $transaksiService->simpan(
            $request->validated('tanggal'),
            $request->validated('item'),
            $request->user()->id
        );

        return redirect("/transaksi/{$transaksi->id}")
            ->with('berhasil', 'Transaksi berhasil disimpan.');
    }

    // Halaman detail satu transaksi.
    public function show(Request $request, Transaksi $transaksi)
    {
        $transaksi->load(['user', 'detailTransaksi.menu']);
        $tampilkanLaba = $this->bolehLihatLaba($request);

        return view('transaksi.show', compact('transaksi', 'tampilkanLaba'));
    }

    // HPP dan laba kotor hanya boleh dilihat owner. Kasir hanya melihat harga jual dan total.
    // Pemeriksaan ini di server: untuk kasir, kolom HPP dan laba tidak dikirim sama sekali ke browser.
    private function bolehLihatLaba(Request $request): bool
    {
        return $request->user()->peran === 'owner';
    }
}
