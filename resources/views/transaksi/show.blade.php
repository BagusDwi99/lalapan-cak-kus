@extends('layouts.app')

@section('judul', 'Detail Transaksi')

@section('konten')
    {{-- Tanggal dan kasir ditampilkan sebagai keterangan di bawah judul --}}
    <x-kepala-halaman :judul="'Detail Transaksi #' . $transaksi->id"
                      :keterangan="'Tanggal ' . $transaksi->tanggal->format('d/m/Y') . ' · Dicatat ' . $transaksi->created_at->locale('id')->translatedFormat('l, j F Y') . ' pukul ' . $transaksi->created_at->format('H:i:s') . ' WITA oleh ' . $transaksi->user->name">
        <a href="/transaksi" class="btn btn-outline-secondary">Kembali</a>
    </x-kepala-halaman>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Menu</th>
                        <th class="text-end">Jumlah Porsi</th>
                        <th class="text-end">Harga Jual</th>
                        <th class="text-end">Subtotal</th>
                        {{-- Hanya owner. @if dijalankan di server, jadi untuk kasir kolom ini tidak ada di HTML --}}
                        @if ($tampilkanLaba)
                            <th class="text-end">HPP / Porsi</th>
                            <th class="text-end">Laba Kotor</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaksi->detailTransaksi as $detail)
                        <tr>
                            <td>{{ $detail->menu->nama }}</td>
                            <td class="text-end">{{ $detail->jumlah }}</td>
                            <td class="text-end">{{ Number::currency($detail->harga_jual, in: 'IDR', locale: 'id') }}</td>
                            {{-- Subtotal = harga jual x jumlah porsi --}}
                            <td class="text-end">{{ Number::currency($detail->harga_jual * $detail->jumlah, in: 'IDR', locale: 'id') }}</td>
                            @if ($tampilkanLaba)
                                <td class="text-end">{{ Number::currency($detail->hpp, in: 'IDR', locale: 'id') }}</td>
                                <td class="text-end">{{ Number::currency($detail->laba_kotor, in: 'IDR', locale: 'id') }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light fw-semibold">
                    <tr>
                        <td>Total</td>
                        <td class="text-end">{{ $transaksi->detailTransaksi->sum('jumlah') }}</td>
                        <td></td>
                        <td class="text-end">{{ Number::currency($transaksi->total, in: 'IDR', locale: 'id') }}</td>
                        @if ($tampilkanLaba)
                            <td></td>
                            <td class="text-end">{{ Number::currency($transaksi->detailTransaksi->sum('laba_kotor'), in: 'IDR', locale: 'id') }}</td>
                        @endif
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
