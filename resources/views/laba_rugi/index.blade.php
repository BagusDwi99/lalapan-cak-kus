@extends('layouts.app')

@section('judul', 'Laba Rugi')

@section('konten')
    <x-kepala-halaman judul="Laporan Laba Rugi" keterangan="Penjualan, HPP, biaya operasional, dan laba bersih per bulan" />

    <form method="GET" action="/laba-rugi" class="row g-2 align-items-end jarak-bagian">
        @include('partials.pilih_periode', ['bulanTerpilih' => $bulan, 'tahunTerpilih' => $tahun])
        <div class="col-auto">
            <button type="submit" class="btn btn-saring">Tampilkan</button>
        </div>
    </form>

    <div class="card kartu-form">
        <div class="card-header fw-semibold">Periode {{ $namaPeriode }}</div>
        {{-- Tabel langsung di dalam kartu, supaya tepi kiri-kanannya sejajar dengan judul kartu --}}
        <table class="table">
                <tbody>
                    <tr>
                        <td>Total Penjualan</td>
                        <td class="text-end">{{ Number::currency($totalPenjualan, in: 'IDR', locale: 'id') }}</td>
                    </tr>
                    <tr>
                        <td>Total HPP</td>
                        <td class="text-end">({{ Number::currency($totalHpp, in: 'IDR', locale: 'id') }})</td>
                    </tr>
                    <tr class="fw-semibold table-light">
                        <td>Laba Kotor</td>
                        <td class="text-end">{{ Number::currency($labaKotor, in: 'IDR', locale: 'id') }}</td>
                    </tr>

                    <tr>
                        <td colspan="2" class="pt-3">Biaya Operasional:</td>
                    </tr>
                    @forelse ($daftarBiaya as $biaya)
                        <tr>
                            {{-- Menjorok ke dalam sebagai rincian biaya --}}
                            <td style="padding-left: 36px !important;">{{ $biaya->nama }}</td>
                            <td class="text-end">{{ Number::currency($biaya->jumlah, in: 'IDR', locale: 'id') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="sel-kosong">Belum ada biaya operasional pada bulan ini.</td>
                        </tr>
                    @endforelse
                    <tr>
                        <td>Total Biaya Operasional</td>
                        <td class="text-end">({{ Number::currency($totalBiaya, in: 'IDR', locale: 'id') }})</td>
                    </tr>

                    {{-- Laba bersih negatif (rugi) ditampilkan merah --}}
                    <tr class="fw-bold fs-5 {{ $labaBersih < 0 ? 'table-danger text-danger' : 'table-success' }}">
                        <td>{{ $labaBersih < 0 ? 'Rugi Bersih' : 'Laba Bersih' }}</td>
                        <td class="text-end">{{ Number::currency($labaBersih, in: 'IDR', locale: 'id') }}</td>
                    </tr>
                </tbody>
            </table>
    </div>

    <p class="small text-muted mt-2">
        Laba kotor = total penjualan &minus; total HPP. Laba bersih = laba kotor &minus; total biaya operasional.
        Angka dalam kurung adalah pengurang.
    </p>
@endsection
