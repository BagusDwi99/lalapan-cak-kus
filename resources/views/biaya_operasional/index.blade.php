@extends('layouts.app')

@section('judul', 'Biaya Operasional')

@section('konten')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Biaya Operasional</h1>
        <a href="/biaya-operasional/create" class="btn btn-primary">Tambah</a>
    </div>

    <form method="GET" action="/biaya-operasional" class="row g-2 align-items-end mb-3">
        @include('partials.pilih_periode', [
            'bulanTerpilih' => $filter['bulan'],
            'tahunTerpilih' => $filter['tahun'],
            'pilihanSemua' => true,
        ])
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary">Saring</button>
            @if (array_filter($filter))
                <a href="/biaya-operasional" class="btn btn-outline-secondary">Reset</a>
            @endif
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama Biaya</th>
                        <th class="text-end">Jumlah</th>
                        <th>Periode</th>
                        <th class="text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Satu kelompok = satu bulan --}}
                    @forelse ($kelompokBiaya as $daftarBiaya)
                        @php $namaPeriode = $daftarBiaya->first()->periode->locale('id')->translatedFormat('F Y'); @endphp

                        <tr class="table-secondary">
                            <th colspan="4">{{ $namaPeriode }}</th>
                        </tr>

                        @foreach ($daftarBiaya as $biaya)
                            <tr>
                                <td>{{ $biaya->nama }}</td>
                                <td class="text-end">{{ Number::currency($biaya->jumlah, in: 'IDR', locale: 'id') }}</td>
                                <td>{{ $namaPeriode }}</td>
                                <td class="text-center">
                                    <a href="/biaya-operasional/{{ $biaya->id }}/edit" class="btn btn-sm btn-warning">Ubah</a>
                                    <form method="POST" action="/biaya-operasional/{{ $biaya->id }}" class="d-inline"
                                          data-pesan="Hapus biaya {{ $biaya->nama }} periode {{ $namaPeriode }}?"
                                          onsubmit="return confirm(this.dataset.pesan)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                        {{-- Subtotal bulan ini --}}
                        <tr class="fw-semibold">
                            <td class="text-end">Subtotal {{ $namaPeriode }}</td>
                            <td class="text-end">{{ Number::currency($daftarBiaya->sum('jumlah'), in: 'IDR', locale: 'id') }}</td>
                            <td colspan="2"></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                {{ array_filter($filter) ? 'Tidak ada biaya operasional pada periode ini.' : 'Belum ada biaya operasional.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
