@extends('layouts.app')

@section('judul', 'Bahan Baku')

@section('konten')
    {{-- Judul di kiri, tombol Tambah di kanan --}}
    <x-kepala-halaman judul="Bahan Baku" keterangan="Daftar bahan baku beserta stok, stok minimum, dan harga rata-rata">
        <a href="/bahan-baku/create" class="btn btn-primary">Tambah</a>
    </x-kepala-halaman>

    {{-- Pencarian memakai GET supaya kata pencarian tampil di alamat URL --}}
    <form method="GET" action="/bahan-baku" class="d-flex gap-2 jarak-bagian" style="max-width: 420px;">
        <input type="text" name="cari" value="{{ $cari }}" class="form-control" placeholder="Cari nama bahan baku">
        <button type="submit" class="btn btn-saring">Cari</button>
        @if ($cari)
            <a href="/bahan-baku" class="btn btn-saring">Reset</a>
        @endif
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Satuan</th>
                        <th class="text-end">Stok</th>
                        <th class="text-end">Stok Minimum</th>
                        <th class="text-end">Harga Rata-rata</th>
                        <th class="text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarBahanBaku as $bahanBaku)
                        {{-- Stok di bawah atau sama dengan stok minimum: baris diberi warna merah --}}
                        @php $stokMenipis = $bahanBaku->stok <= $bahanBaku->stok_minimum; @endphp

                        <tr class="{{ $stokMenipis ? 'table-danger' : '' }}">
                            <td>
                                {{ $bahanBaku->nama }}
                                @if ($stokMenipis)
                                    <span class="badge bg-danger ms-1">Stok menipis</span>
                                @endif
                            </td>
                            <td>{{ $bahanBaku->satuan }}</td>
                            {{-- Number::format menampilkan angka gaya Indonesia, misalnya 182,5 --}}
                            <td class="text-end">{{ Number::format($bahanBaku->stok, maxPrecision: 3, locale: 'id') }}</td>
                            <td class="text-end">{{ Number::format($bahanBaku->stok_minimum, maxPrecision: 3, locale: 'id') }}</td>
                            {{-- Number::currency menampilkan rupiah, misalnya Rp 8.639,61 --}}
                            <td class="text-end">{{ Number::currency($bahanBaku->harga_rata_rata, in: 'IDR', locale: 'id') }}</td>
                            <td class="text-center">
                                <a href="/bahan-baku/{{ $bahanBaku->id }}/edit" class="btn btn-sm btn-outline-primary">Ubah</a>

                                {{-- Hapus memakai formulir DELETE, dengan konfirmasi terlebih dahulu.
                                     Pesan konfirmasi disimpan di data-pesan supaya aman walaupun
                                     nama bahan baku mengandung tanda kutip. --}}
                                <form method="POST" action="/bahan-baku/{{ $bahanBaku->id }}" class="d-inline"
                                      data-pesan="Hapus bahan baku {{ $bahanBaku->nama }}?"
                                      onsubmit="return confirm(this.dataset.pesan)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="sel-kosong">
                                {{ $cari ? "Bahan baku dengan nama \"{$cari}\" tidak ditemukan." : 'Belum ada data bahan baku.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tombol pindah halaman, 10 baris per halaman --}}
    <div class="mt-3">
        {{ $daftarBahanBaku->links() }}
    </div>
@endsection
