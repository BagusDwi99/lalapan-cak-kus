<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul') — Warung Lalapan Cak Kus</title>
    {{-- Bootstrap disimpan lokal di public/vendor/bootstrap, jadi tetap tampil tanpa internet.
         asset() menghasilkan alamat lengkap ke folder public. --}}
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
@php
    // Daftar menu sidebar.
    // url    = alamat halaman
    // aktif  = pola alamat untuk menandai menu yang sedang dibuka (tanda * = termasuk sub-halaman)
    // owner  = true jika menu hanya tampil untuk owner
    $daftarMenu = [
        ['judul' => 'Dasbor',                   'url' => '/dasbor',            'aktif' => 'dasbor*',            'owner' => false],
        ['judul' => 'Bahan Baku',               'url' => '/bahan-baku',        'aktif' => 'bahan-baku*',        'owner' => false],
        ['judul' => 'Barang Masuk',             'url' => '/barang-masuk',      'aktif' => 'barang-masuk*',      'owner' => false],
        ['judul' => 'Transaksi Penjualan',      'url' => '/transaksi',         'aktif' => 'transaksi*',         'owner' => false],
        ['judul' => 'Biaya Operasional',        'url' => '/biaya-operasional', 'aktif' => 'biaya-operasional*', 'owner' => true],
        ['judul' => 'Prediksi Kebutuhan Stok',  'url' => '/prediksi',          'aktif' => 'prediksi*',          'owner' => true],
        ['judul' => 'Laporan',                  'url' => '/laporan',           'aktif' => 'laporan*',           'owner' => true],
        ['judul' => 'Tutup Buku',               'url' => '/tutup-buku',        'aktif' => 'tutup-buku*',        'owner' => false],
    ];

    $pengguna = auth()->user();
@endphp

<div class="d-flex min-vh-100">
    {{-- Sidebar gelap di sebelah kiri --}}
    <aside class="d-flex flex-column bg-dark text-white p-3 flex-shrink-0" style="width: 250px;">
        <a href="/dasbor" class="text-white text-decoration-none fs-5 fw-semibold mb-3">
            Lalapan Cak Kus
        </a>
        <hr class="mt-0">

        {{-- flex-grow-1 mendorong bagian pengguna ke bawah sidebar --}}
        <ul class="nav nav-pills flex-column flex-grow-1">
            @foreach ($daftarMenu as $menu)
                {{-- Lewati menu khusus owner jika pengguna bukan owner --}}
                @if ($menu['owner'] && $pengguna->peran !== 'owner')
                    @continue
                @endif

                <li class="nav-item mb-1">
                    {{-- request()->is() bernilai true jika alamat yang dibuka cocok dengan pola --}}
                    <a href="{{ $menu['url'] }}"
                       class="nav-link {{ request()->is($menu['aktif']) ? 'active' : 'text-white' }}">
                        {{ $menu['judul'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        <hr>
        {{-- Nama pengguna yang sedang masuk dan tombol keluar --}}
        <div>
            <div class="fw-semibold">{{ $pengguna->name }}</div>
            <div class="small text-white-50 mb-2">{{ ucfirst($pengguna->peran) }}</div>

            {{-- Keluar memakai POST, bukan link biasa, supaya aman dari CSRF --}}
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm w-100">Keluar</button>
            </form>
        </div>
    </aside>

    {{-- Isi halaman --}}
    <main class="flex-grow-1 p-4">
        {{-- Pesan berhasil atau gagal dari controller, contoh: ->with('berhasil', '...').
             Berlaku untuk semua halaman. alert-dismissible + tombol close = bisa ditutup. --}}
        @if (session('berhasil'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('berhasil') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif
        @if (session('gagal'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('gagal') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif

        @yield('konten')
    </main>
</div>

{{-- JavaScript Bootstrap (versi bundle sudah termasuk Popper) untuk komponen
     seperti dropdown, modal, dan alert yang bisa ditutup.
     Ditaruh di akhir body supaya halaman tampil lebih dulu. --}}
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
