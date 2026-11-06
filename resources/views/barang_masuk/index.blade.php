@extends('layouts.app')

@section('judul', 'Barang Masuk')

@section('konten')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Barang Masuk</h1>
        <a href="/barang-masuk/create" class="btn btn-primary">Tambah</a>
    </div>

    <div class="alert alert-info">
        <strong>Catatan:</strong> barang masuk tidak bisa diubah atau dihapus, seperti jurnal akuntansi.
        Setiap pencatatan menghitung ulang harga rata-rata bahan baku dari stok dan harga rata-rata sebelumnya.
        Jika satu catatan lama diubah, seluruh riwayat harga rata-rata sesudahnya menjadi tidak sahih.
        Periksa kembali data sebelum menyimpan.
    </div>

    {{-- Penyaring: bahan baku dan rentang tanggal --}}
    <form method="GET" action="/barang-masuk" class="row g-2 align-items-end mb-3">
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
            <button type="submit" class="btn btn-outline-secondary">Saring</button>
            @if (array_filter($filter))
                <a href="/barang-masuk" class="btn btn-outline-secondary">Reset</a>
            @endif
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
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
                            <td colspan="6" class="text-center text-muted py-4">
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
