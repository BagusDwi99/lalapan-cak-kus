@extends('layouts.app')

@section('judul', 'Tutup Buku')

@section('konten')
    <x-kepala-halaman judul="Tutup Buku" keterangan="Rekap penjualan harian; satu tanggal hanya bisa ditutup sekali">

        <form method="POST" action="/tutup-buku"
              data-pesan="Tutup buku hari ini ({{ today()->format('d/m/Y') }})? Proses ini hanya bisa dilakukan sekali."
              onsubmit="return confirm(this.dataset.pesan)">
            @csrf
            {{-- Tombol nonaktif jika hari ini sudah ditutup --}}
            <button type="submit" class="btn btn-primary" @disabled($tutupBukuHariIni)>
                Tutup Buku Hari Ini
            </button>
        </form>
    </x-kepala-halaman>

    @if ($tutupBukuHariIni)
        <div class="alert alert-success">
            Hari ini ({{ today()->format('d/m/Y') }}) sudah ditutup pada pukul
            {{ $tutupBukuHariIni->created_at->format('H:i') }}.
            Tutup buku berikutnya bisa dilakukan besok.
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th class="text-end">Total Penjualan</th>
                        {{-- Hanya owner. @if dijalankan di server, jadi untuk kasir kolom ini tidak ada di HTML --}}
                        @if ($tampilkanLaba)
                            <th class="text-end">Total Laba Kotor</th>
                        @endif
                        <th>Waktu Pencatatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarTutupBuku as $tutupBuku)
                        <tr>
                            <td>{{ $tutupBuku->tanggal->format('d/m/Y') }}</td>
                            <td class="text-end">{{ Number::currency($tutupBuku->total_penjualan, in: 'IDR', locale: 'id') }}</td>
                            @if ($tampilkanLaba)
                                <td class="text-end">{{ Number::currency($tutupBuku->total_laba, in: 'IDR', locale: 'id') }}</td>
                            @endif
                            <td>{{ $tutupBuku->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $tampilkanLaba ? 4 : 3 }}" class="sel-kosong">Belum ada tutup buku.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $daftarTutupBuku->links() }}
    </div>

    {{-- Modal notifikasi stok minimum, hanya dibuat tepat setelah tutup buku berhasil --}}
    @if ($tampilkanModalStok)
        <div class="modal fade" id="modal-stok" tabindex="-1" aria-labelledby="judul-modal-stok" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header {{ $bahanMenipis->isNotEmpty() ? 'kepala-peringatan' : 'kepala-aman' }}">
                        <h2 class="modal-title fs-5" id="judul-modal-stok">
                            {{ $bahanMenipis->isNotEmpty() ? 'Peringatan Stok Menipis' : 'Stok Aman' }}
                        </h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        @if ($bahanMenipis->isNotEmpty())
                            <p>Bahan baku berikut stoknya sudah kurang dari atau sama dengan stok minimum:</p>
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Bahan Baku</th>
                                        <th class="text-end">Stok Saat Ini</th>
                                        <th class="text-end">Stok Minimum</th>
                                        <th class="text-end">Selisih (Stok &minus; Minimum)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bahanMenipis as $bahan)
                                        <tr>
                                            <td>{{ $bahan->nama }}</td>
                                            <td class="text-end">{{ Number::format($bahan->stok, maxPrecision: 3, locale: 'id') }} {{ $bahan->satuan }}</td>
                                            <td class="text-end">{{ Number::format($bahan->stok_minimum, maxPrecision: 3, locale: 'id') }} {{ $bahan->satuan }}</td>
                                            {{-- Selisih nol atau negatif: stok sudah di batas atau di bawah minimum --}}
                                            <td class="text-end text-danger fw-semibold">
                                                {{ Number::format($bahan->stok - $bahan->stok_minimum, maxPrecision: 3, locale: 'id') }} {{ $bahan->satuan }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="mb-0">Seluruh stok bahan baku masih aman, tidak ada yang berada di bawah atau sama dengan stok minimum.</p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <a href="/barang-masuk/create" class="btn btn-primary">Ke Halaman Barang Masuk</a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Tampilkan modal otomatis setelah halaman selesai dimuat.
            // DOMContentLoaded menunggu sampai JavaScript Bootstrap (dimuat di akhir layout) siap.
            document.addEventListener('DOMContentLoaded', function () {
                new bootstrap.Modal(document.getElementById('modal-stok')).show();
            });
        </script>
    @endif
@endsection
