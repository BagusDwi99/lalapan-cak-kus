@extends('layouts.app')

@section('judul', 'Menu')

@section('konten')
    <x-kepala-halaman judul="Menu" keterangan="Daftar menu beserta harga jual dan komposisi bahan bakunya">
        <a href="/menu/create" class="btn btn-primary">Tambah</a>
    </x-kepala-halaman>

    <form method="GET" action="/menu" class="d-flex gap-2 jarak-bagian" style="max-width: 420px;">
        <input type="text" name="cari" value="{{ $cari }}" class="form-control" placeholder="Cari nama menu">
        <button type="submit" class="btn btn-saring">Cari</button>
        @if ($cari)
            <a href="/menu" class="btn btn-saring">Reset</a>
        @endif
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama Menu</th>
                        <th class="text-end">Harga Jual</th>
                        <th class="text-end">Jumlah Bahan</th>
                        {{-- Hanya owner. @if dijalankan di server, jadi untuk kasir kolom ini tidak ada di HTML --}}
                        @if ($tampilkanLaba)
                            <th class="text-end">Perkiraan HPP</th>
                            <th class="text-end">Perkiraan Laba Kotor / Porsi</th>
                        @endif
                        <th class="text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarMenu as $menu)
                        <tr>
                            <td>{{ $menu->nama }}</td>
                            <td class="text-end">{{ Number::currency($menu->harga_jual, in: 'IDR', locale: 'id') }}</td>
                            <td class="text-end">{{ $menu->bahanBaku->count() }} bahan</td>
                            @if ($tampilkanLaba)
                                <td class="text-end">{{ Number::currency($menu->perkiraan_hpp, in: 'IDR', locale: 'id') }}</td>
                                {{-- Laba negatif (rugi) ditampilkan merah --}}
                                <td class="text-end {{ $menu->perkiraan_laba < 0 ? 'text-danger fw-semibold' : '' }}">
                                    {{ Number::currency($menu->perkiraan_laba, in: 'IDR', locale: 'id') }}
                                </td>
                            @endif
                            <td class="text-center">
                                <a href="/menu/{{ $menu->id }}/edit" class="btn btn-sm btn-outline-primary">Ubah</a>
                                <form method="POST" action="/menu/{{ $menu->id }}" class="d-inline"
                                      data-pesan="Hapus menu {{ $menu->nama }}?"
                                      onsubmit="return confirm(this.dataset.pesan)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $tampilkanLaba ? 6 : 4 }}" class="sel-kosong">
                                {{ $cari ? "Menu dengan nama \"{$cari}\" tidak ditemukan." : 'Belum ada data menu.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($tampilkanLaba)
        <p class="small text-muted mt-2 mb-0">
            Perkiraan HPP = jumlah pemakaian tiap bahan &times; harga rata-rata bahan saat ini.
            HPP sebenarnya dicatat saat transaksi penjualan terjadi.
        </p>
    @endif

    <div class="mt-3">
        {{ $daftarMenu->links() }}
    </div>
@endsection
