<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuRequest;
use App\Models\BahanBaku;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Mengatur halaman daftar, tambah, ubah, dan hapus menu beserta komposisinya.
class MenuController extends Controller
{
    // Halaman daftar menu dengan perkiraan HPP dan laba kotor per porsi.
    public function index(Request $request)
    {
        $cari = $request->query('cari');

        $daftarMenu = Menu::query()
            // with('bahanBaku'): ambil komposisi sekaligus, supaya tidak query berulang per menu.
            ->with('bahanBaku')
            ->when($cari, fn ($query) => $query->where('nama', 'like', "%{$cari}%"))
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        // Perkiraan HPP dan laba hanya untuk owner, sama seperti di modul transaksi.
        $tampilkanLaba = $request->user()->bolehLihatLaba();

        // Hitung perkiraan HPP (method model Menu) dan laba kotor untuk setiap menu di halaman ini.
        // Nilainya hanya untuk ditampilkan, tidak disimpan ke basis data.
        // Untuk kasir tidak dihitung sama sekali, jadi angkanya tidak pernah sampai ke tampilan.
        if ($tampilkanLaba) {
            foreach ($daftarMenu as $menu) {
                $menu->perkiraan_hpp = $menu->hitungHppPerPorsi();
                $menu->perkiraan_laba = $menu->harga_jual - $menu->perkiraan_hpp;
            }
        }

        return view('menu.index', compact('daftarMenu', 'cari', 'tampilkanLaba'));
    }

    // Formulir tambah menu.
    public function create()
    {
        $menu = new Menu();

        // Jika validasi gagal, tampilkan lagi baris komposisi yang tadi diisi.
        // Jika tidak, tampilkan satu baris kosong.
        $komposisi = old('komposisi', [['bahan_baku_id' => null, 'jumlah' => null]]);

        return view('menu.create', [
            'menu' => $menu,
            'komposisi' => $komposisi,
            'daftarBahan' => $this->daftarBahan(),
        ]);
    }

    // Menyimpan menu baru beserta komposisinya.
    public function store(MenuRequest $request)
    {
        // DB::transaction: semua perintah di dalamnya berhasil semua, atau batal semua.
        $menu = DB::transaction(function () use ($request) {
            $menu = Menu::create($request->dataMenu());

            // sync() mengisi tabel menu_bahan_baku sesuai daftar komposisi.
            $menu->bahanBaku()->sync($request->dataKomposisi());

            return $menu;
        });

        return redirect('/menu')->with('berhasil', "Menu {$menu->nama} berhasil ditambahkan.");
    }

    // Formulir ubah menu, komposisi lama langsung tampil terisi.
    public function edit(Menu $menu)
    {
        // Ubah komposisi dari basis data ke bentuk baris formulir:
        // [['bahan_baku_id' => 1, 'jumlah' => 1], ['bahan_baku_id' => 6, 'jumlah' => 0.15], ...]
        $komposisiLama = $menu->bahanBaku->map(fn ($bahan) => [
            'bahan_baku_id' => $bahan->id,
            'jumlah' => $bahan->pivot->jumlah + 0, // + 0 mengubah "0.150" menjadi 0.15
        ])->all();

        $komposisi = old('komposisi', $komposisiLama);

        return view('menu.edit', [
            'menu' => $menu,
            'komposisi' => $komposisi,
            'daftarBahan' => $this->daftarBahan(),
        ]);
    }

    // Menyimpan perubahan menu beserta komposisinya.
    public function update(MenuRequest $request, Menu $menu)
    {
        DB::transaction(function () use ($request, $menu) {
            $menu->update($request->dataMenu());

            // sync() otomatis menambah bahan baru, mengubah jumlah yang berubah,
            // dan menghapus bahan yang tidak ada lagi di formulir.
            $menu->bahanBaku()->sync($request->dataKomposisi());
        });

        return redirect('/menu')->with('berhasil', "Menu {$menu->nama} berhasil diubah.");
    }

    // Menghapus menu, kecuali jika sudah pernah terjual.
    public function destroy(Menu $menu)
    {
        $jumlahTerjual = $menu->detailTransaksi()->count();

        if ($jumlahTerjual > 0) {
            return back()->with('gagal',
                "Menu {$menu->nama} tidak dapat dihapus karena sudah pernah terjual "
                . "(tercatat di {$jumlahTerjual} transaksi). Data ini dibutuhkan untuk laporan penjualan dan laba.");
        }

        $nama = $menu->nama;

        // Baris komposisi di menu_bahan_baku ikut terhapus otomatis (cascade di migration).
        $menu->delete();

        return redirect('/menu')->with('berhasil', "Menu {$nama} berhasil dihapus.");
    }

    // Daftar bahan baku untuk pilihan dropdown di formulir.
    private function daftarBahan()
    {
        return BahanBaku::orderBy('nama')->get(['id', 'nama', 'satuan']);
    }
}
