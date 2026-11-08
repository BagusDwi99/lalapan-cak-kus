@extends('layouts.app')

@section('judul', 'Riwayat Transaksi')

@section('konten')
    <x-kepala-halaman judul="Riwayat Transaksi" keterangan="Daftar transaksi penjualan yang sudah tercatat">
        {{-- Transaksi baru dicatat di halaman Kasir --}}
        <a href="/kasir" class="btn btn-primary">Transaksi Baru</a>
    </x-kepala-halaman>

    <div class="alert alert-info">
        <strong>Catatan:</strong> transaksi penjualan tidak bisa diubah atau dihapus.
        Setiap transaksi sudah mengurangi stok bahan baku.
    </div>

    <form method="GET" action="/transaksi" class="row g-2 align-items-end jarak-bagian">
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
                <a href="/transaksi" class="btn btn-saring">Reset</a>
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
                            {{-- Jam pencatatan dari kolom created_at (zona waktu WITA) --}}
                            <td class="text-end">{{ $transaksi->created_at->format('H:i:s') }}</td>
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
                            <td colspan="{{ $tampilkanLaba ? 7 : 6 }}" class="sel-kosong">
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
