@extends('layouts.app')

@section('judul', 'Detail Transaksi')

@section('konten')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Detail Transaksi #{{ $transaksi->id }}</h1>
        <a href="/transaksi" class="btn btn-secondary">Kembali</a>
    </div>

    <dl class="row mb-3" style="max-width: 500px;">
        <dt class="col-4">Tanggal</dt>
        <dd class="col-8">{{ $transaksi->tanggal->format('d/m/Y') }}</dd>
        <dt class="col-4">Kasir</dt>
        <dd class="col-8">{{ $transaksi->user->name }}</dd>
    </dl>

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
