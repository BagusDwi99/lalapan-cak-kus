@extends('layouts.app')

@section('judul', 'Barang Masuk')

@section('konten')
    <x-kepala-halaman judul="Barang Masuk" keterangan="Riwayat pembelian bahan baku. Barang masuk tidak dapat diubah atau dihapus.">
        <a href="/barang-masuk/create" class="btn btn-primary">Tambah</a>
    </x-kepala-halaman>

    {{-- Penyaring: bahan baku dan rentang tanggal --}}
    <form method="GET" action="/barang-masuk" class="row g-2 align-items-end jarak-bagian">
        <div class="col-md-3">
            <label for="bahan_baku_id" class="form-label small mb-1">Bahan Baku</label>
            <select id="bahan_baku_id" name="bahan_baku_id" class="form-select">
                <option value="">Semua bahan baku</option>
                @foreach ($daftarBahan as $bahan)
                    <option value="{{ $bahan->id }}" @selected($filter['bahan_baku_id'] == $bahan->id)>{{ $bahan->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="dari" class="form-label small mb-1">Dari Tanggal</label>
            <input type="date" id="dari" name="dari" value="{{ $filter['dari'] }}" class="form-control">
        </div>
        <div class="col-md-2">
            <label for="sampai" class="form-label small mb-1">Sampai Tanggal</label>
            <input type="date" id="sampai" name="sampai" value="{{ $filter['sampai'] }}" class="form-control">
        </div>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-saring">Saring</button>
            @if (array_filter($filter))
                <a href="/barang-masuk" class="btn btn-saring">Reset</a>
            @endif
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th class="text-end">Waktu Dicatat</th>
                        <th>Bahan Baku</th>
                        <th class="text-end">Jumlah</th>
                        <th class="text-end">Harga Beli / Satuan</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Harga Rata-rata Setelahnya</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarBarangMasuk as $barangMasuk)
                        <tr>
                            <td>{{ $barangMasuk->tanggal->format('d/m/Y') }}</td>
                            {{-- Jam pencatatan dari kolom created_at (zona waktu WITA) --}}
                            <td class="text-end">{{ $barangMasuk->created_at->format('H:i:s') }}</td>
                            <td>{{ $barangMasuk->bahanBaku->nama }}</td>
                            <td class="text-end">
                                {{ Number::format($barangMasuk->jumlah, maxPrecision: 3, locale: 'id') }}
                                {{ $barangMasuk->bahanBaku->satuan }}
                            </td>
                            <td class="text-end">{{ Number::currency($barangMasuk->harga_beli, in: 'IDR', locale: 'id') }}</td>
                            {{-- Total = jumlah x harga beli per satuan --}}
                            <td class="text-end">{{ Number::currency($barangMasuk->jumlah * $barangMasuk->harga_beli, in: 'IDR', locale: 'id') }}</td>
                            <td class="text-end">{{ Number::currency($barangMasuk->harga_rata_rata_setelah, in: 'IDR', locale: 'id') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="sel-kosong">
                                {{ array_filter($filter) ? 'Tidak ada barang masuk yang sesuai penyaring.' : 'Belum ada data barang masuk.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $daftarBarangMasuk->links() }}
    </div>
@endsection
