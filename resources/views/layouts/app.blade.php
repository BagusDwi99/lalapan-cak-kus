<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul') — Warung Lalapan Cak Kus</title>
    {{-- Bootstrap disimpan lokal di public/vendor/bootstrap, jadi tetap tampil tanpa internet.
         asset() menghasilkan alamat lengkap ke folder public. --}}
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    {{-- Gaya tampilan aplikasi (huruf, palet, komponen), dipakai juga oleh halaman login --}}
    @include('partials.gaya')
</head>
<body>
@php
    // Daftar menu sidebar.
    // url    = alamat halaman
    // aktif  = pola alamat untuk menandai menu yang sedang dibuka (tanda * = termasuk sub-halaman)
    // owner  = true jika menu hanya tampil untuk owner
    $daftarMenu = [
        ['judul' => 'Dasbor',                   'url' => '/dasbor',            'aktif' => 'dasbor*',            'owner' => false],
        ['judul' => 'Bahan Baku',               'url' => '/bahan-baku',        'aktif' => 'bahan-baku*',        'owner' => false],
        ['judul' => 'Menu',                     'url' => '/menu',              'aktif' => 'menu*',              'owner' => false],
        ['judul' => 'Barang Masuk',             'url' => '/barang-masuk',      'aktif' => 'barang-masuk*',      'owner' => false],
        ['judul' => 'Transaksi Penjualan',      'url' => '/transaksi',         'aktif' => 'transaksi*',         'owner' => false],
        ['judul' => 'Biaya Operasional',        'url' => '/biaya-operasional', 'aktif' => 'biaya-operasional*', 'owner' => true],
        ['judul' => 'Prediksi Kebutuhan Stok',  'url' => '/prediksi',          'aktif' => 'prediksi*',          'owner' => true],
        ['judul' => 'Laba Rugi',                'url' => '/laba-rugi',         'aktif' => 'laba-rugi*',         'owner' => true],
        ['judul' => 'Laporan',                  'url' => '/laporan',           'aktif' => 'laporan*',           'owner' => true],
        ['judul' => 'Tutup Buku',               'url' => '/tutup-buku',        'aktif' => 'tutup-buku*',        'owner' => false],
    ];

    $pengguna = auth()->user();
@endphp

<div class="d-flex min-vh-100">
    {{-- Sidebar gelap di sebelah kiri. d-print-none: tidak ikut tercetak. --}}
    <aside class="sidebar d-flex flex-column flex-shrink-0 d-print-none">
        <a href="/dasbor" class="sidebar-nama">Lalapan Cak Kus</a>
        <div class="sidebar-sub">Sistem Pencatatan Stok &amp; Laba</div>
        <hr class="sidebar-garis">

        {{-- flex-grow-1 mendorong bagian pengguna ke bawah sidebar --}}
        <ul class="nav flex-column flex-grow-1">
            @foreach ($daftarMenu as $menu)
                {{-- Lewati menu khusus owner jika pengguna bukan owner --}}
                @if ($menu['owner'] && $pengguna->peran !== 'owner')
                    @continue
                @endif

                <li class="nav-item">
                    {{-- request()->is() bernilai true jika alamat yang dibuka cocok dengan pola --}}
                    <a href="{{ $menu['url'] }}"
                       class="nav-link {{ request()->is($menu['aktif']) ? 'active' : '' }}"
                       @if (request()->is($menu['aktif'])) aria-current="page" @endif>
                        {{ $menu['judul'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        <hr class="sidebar-garis">
        {{-- Nama pengguna yang sedang masuk dan tombol keluar --}}
        <div>
            <div class="sidebar-pengguna">{{ $pengguna->name }}</div>
            <div class="sidebar-peran">{{ ucfirst($pengguna->peran) }}</div>

            {{-- Keluar memakai POST, bukan link biasa, supaya aman dari CSRF --}}
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="btn btn-keluar btn-sm w-100">Keluar</button>
            </form>
        </div>
    </aside>

    {{-- Isi halaman --}}
    <main class="isi-halaman flex-grow-1">
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
