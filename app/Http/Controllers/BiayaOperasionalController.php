<?php

namespace App\Http\Controllers;

use App\Http\Requests\BiayaOperasionalRequest;
use App\Models\BiayaOperasional;
use Illuminate\Http\Request;

// Mengatur biaya operasional bulanan (khusus owner).
// Berbeda dengan barang masuk, biaya operasional boleh diubah dan dihapus,
// karena hanya catatan beban bulanan dan tidak memengaruhi stok maupun harga rata-rata.
class BiayaOperasionalController extends Controller
{
    // Halaman daftar, dikelompokkan per bulan, dengan penyaring bulan dan tahun.
    public function index(Request $request)
    {
        $filter = [
            'bulan' => $request->query('bulan'),
            'tahun' => $request->query('tahun'),
        ];

        $daftarBiaya = BiayaOperasional::query()
            ->when($filter['bulan'], fn ($query, $bulan) => $query->whereMonth('periode', $bulan))
            ->when($filter['tahun'], fn ($query, $tahun) => $query->whereYear('periode', $tahun))
            ->orderByDesc('periode')
            ->orderBy('nama')
            ->get();

        // Kelompokkan per bulan, contoh: ['2026-11' => [biaya...], '2026-10' => [biaya...]]
        $kelompokBiaya = $daftarBiaya->groupBy(fn ($biaya) => $biaya->periode->format('Y-m'));

        return view('biaya_operasional.index', compact('kelompokBiaya', 'filter'));
    }

    public function create()
    {
        return view('biaya_operasional.create', [
            'biaya' => new BiayaOperasional(['periode' => now()->startOfMonth()]),
        ]);
    }

    public function store(BiayaOperasionalRequest $request)
    {
        $biaya = BiayaOperasional::create($request->dataBiaya());

        return redirect('/biaya-operasional')
            ->with('berhasil', "Biaya {$biaya->nama} berhasil ditambahkan.");
    }

    public function edit(BiayaOperasional $biayaOperasional)
    {
        return view('biaya_operasional.edit', ['biaya' => $biayaOperasional]);
    }

    public function update(BiayaOperasionalRequest $request, BiayaOperasional $biayaOperasional)
    {
        $biayaOperasional->update($request->dataBiaya());

        return redirect('/biaya-operasional')
            ->with('berhasil', "Biaya {$biayaOperasional->nama} berhasil diubah.");
    }

    public function destroy(BiayaOperasional $biayaOperasional)
    {
        $nama = $biayaOperasional->nama;
        $biayaOperasional->delete();

        return redirect('/biaya-operasional')->with('berhasil', "Biaya {$nama} berhasil dihapus.");
    }
}
