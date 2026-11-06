@extends('layouts.app')

@section('judul', 'Transaksi Penjualan')

@section('konten')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Transaksi Penjualan</h1>
        <a href="/transaksi/create" class="btn btn-primary">Transaksi Baru</a>
    </div>

    <div class="alert alert-info">
        <strong>Catatan:</strong> transaksi penjualan tidak bisa diubah atau dihapus.
        Setiap transaksi sudah mengurangi stok bahan baku.
    </div>

    <form method="GET" action="/transaksi" class="row g-2 align-items-end mb-3">
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
                <a href="/transaksi" class="btn btn-outline-secondary">Reset</a>
            @endif
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th class="text-end">Jumlah Item</th>
                        <th class="text-end">Total Penjualan</th>
                        {{-- @if di Blade dijalankan di server: untuk kasir kolom ini tidak ada di HTML --}}
                        @if ($tampilkanLaba)
                            <th class="text-end">Total Laba Kotor</th>
                        @endif
                        <th>Kasir</th>
                        <th class="text-center" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarTransaksi as $transaksi)
                        <tr>
                            <td>{{ $transaksi->tanggal->format('d/m/Y') }}</td>
                            <td class="text-end">{{ $transaksi->jumlah_porsi }} porsi</td>
                            <td class="text-end">{{ Number::currency($transaksi->total, in: 'IDR', locale: 'id') }}</td>
                            @if ($tampilkanLaba)
                                <td class="text-end">{{ Number::currency($transaksi->total_laba, in: 'IDR', locale: 'id') }}</td>
                            @endif
                            <td>{{ $transaksi->user->name }}</td>
                            <td class="text-center">
                                <a href="/transaksi/{{ $transaksi->id }}" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $tampilkanLaba ? 6 : 5 }}" class="text-center text-muted py-4">
                                {{ array_filter($filter) ? 'Tidak ada transaksi pada rentang tanggal ini.' : 'Belum ada transaksi.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $daftarTransaksi->links() }}
    </div>
@endsection
