{{-- Halaman dasbor memakai kerangka layouts/app.blade.php --}}
@extends('layouts.app')

@section('judul', 'Dasbor')

@section('konten')
    @php
        // Format tampilan gaya Indonesia (bukan rumus).
        $rupiah = fn ($nilai) => Number::currency($nilai, in: 'IDR', locale: 'id');
        $angka = fn ($nilai) => Number::format($nilai, maxPrecision: 2, locale: 'id');
    @endphp

    <h1 class="h3 mb-1">Dasbor</h1>
    <p class="text-muted">
        Selamat datang, {{ auth()->user()->name }}.
        Hari ini {{ today()->locale('id')->translatedFormat('l, j F Y') }}.
    </p>

    {{-- Status tutup buku hari ini (owner dan kasir) --}}
    @if ($tutupBukuHariIni)
        <div class="alert alert-success d-flex align-items-center justify-content-between">
            <span><strong>Sudah tutup buku.</strong> Hari ini ditutup pada pukul {{ $tutupBukuHariIni->created_at->format('H:i') }}.</span>
            <a href="/tutup-buku" class="btn btn-sm btn-outline-success">Lihat Tutup Buku</a>
        </div>
    @else
        <div class="alert alert-warning d-flex align-items-center justify-content-between">
            <span><strong>Belum tutup buku.</strong> Lakukan tutup buku di akhir hari operasional.</span>
            <a href="/tutup-buku" class="btn btn-sm btn-outline-dark">Ke Tutup Buku</a>
        </div>
    @endif

    {{-- Kartu ringkasan hari ini. Owner: 4 kartu. Kasir: 3 kartu, tanpa laba. --}}
    <div class="row g-3 mb-4">
        @if ($tampilkanLaba)
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Penjualan Hari Ini</div>
                    <div class="fs-4 fw-semibold">{{ $rupiah($penjualanHariIni) }}</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Laba Kotor Hari Ini</div>
                    <div class="fs-4 fw-semibold">{{ $rupiah($labaHariIni) }}</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Transaksi Hari Ini</div>
                    <div class="fs-4 fw-semibold">{{ $jumlahTransaksiHariIni }} <span class="fs-6 fw-normal">nota</span></div>
                </div></div>
            </div>
        @else
            <div class="col-md-4">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Transaksi Hari Ini</div>
                    <div class="fs-4 fw-semibold">{{ $jumlahTransaksiHariIni }} <span class="fs-6 fw-normal">nota</span></div>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Penjualan Hari Ini</div>
                    <div class="fs-4 fw-semibold">{{ $rupiah($penjualanHariIni) }}</div>
                </div></div>
            </div>
        @endif

        {{-- Kartu bahan menipis (owner dan kasir) --}}
        <div class="{{ $tampilkanLaba ? 'col-sm-6 col-xl-3' : 'col-md-4' }}">
            <div class="card h-100 {{ $jumlahMenipis > 0 ? 'border-danger' : '' }}"><div class="card-body">
                <div class="text-muted small">Bahan Baku Menipis</div>
                <div class="fs-4 fw-semibold {{ $jumlahMenipis > 0 ? 'text-danger' : '' }}">
                    {{ $jumlahMenipis }} <span class="fs-6 fw-normal">bahan</span>
                </div>
                <a href="#daftar-menipis" class="small">Lihat daftar</a>
            </div></div>
        </div>
    </div>

    @if ($tampilkanLaba)
        {{-- Ringkasan bulan berjalan (owner) --}}
        @php $namaBulan = today()->locale('id')->translatedFormat('F Y'); @endphp
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Ringkasan {{ $namaBulan }}</span>
                <a href="/laba-rugi" class="small">Lihat Laba Rugi</a>
            </div>
            <div class="card-body">
                <div class="row text-center g-3">
                    <div class="col-6 col-lg-3">
                        <div class="text-muted small">Penjualan</div>
                        <div class="fs-5 fw-semibold">{{ $rupiah($bulanIni['totalPenjualan']) }}</div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="text-muted small">Laba Kotor</div>
                        <div class="fs-5 fw-semibold">{{ $rupiah($bulanIni['labaKotor']) }}</div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="text-muted small">Biaya Operasional</div>
                        <div class="fs-5 fw-semibold">{{ $rupiah($bulanIni['totalBiaya']) }}</div>
                    </div>
                    <div class="col-6 col-lg-3">
                        {{-- Laba bersih negatif (rugi) ditampilkan merah --}}
                        <div class="text-muted small">{{ $bulanIni['labaBersih'] < 0 ? 'Rugi Bersih' : 'Laba Bersih' }}</div>
                        <div class="fs-5 fw-semibold {{ $bulanIni['labaBersih'] < 0 ? 'text-danger' : 'text-success' }}">
                            {{ $rupiah($bulanIni['labaBersih']) }}
                        </div>
                    </div>
                </div>
                <p class="small text-muted mb-0 mt-3">
                    Biaya operasional dihitung untuk satu bulan penuh, sedangkan penjualan baru sampai hari ini.
                </p>
            </div>
        </div>

        <div class="row g-3 mb-4">
            {{-- Grafik penjualan 14 hari terakhir (owner) --}}
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header fw-semibold">Penjualan Harian 14 Hari Terakhir</div>
                    <div class="card-body">
                        <div style="position: relative; height: 280px;">
                            <canvas id="grafik-penjualan" role="img"
                                    aria-label="Grafik garis total penjualan per hari selama 14 hari terakhir"></canvas>
                        </div>
                        <p class="small text-muted mb-0 mt-2">Titik terakhir adalah hari ini, yang masih berjalan.</p>
                    </div>
                </div>
            </div>

            {{-- Lima menu terlaris bulan ini (owner) --}}
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header fw-semibold">Menu Terlaris {{ $namaBulan }}</div>
                    <div class="card-body p-0">
                        @if (count($menuTerlaris) > 0)
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">#</th>
                                        <th>Menu</th>
                                        <th class="text-end">Porsi</th>
                                        <th class="text-end pe-3">Penjualan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($menuTerlaris as $urutan => $menu)
                                        <tr>
                                            <td class="ps-3">{{ $urutan + 1 }}</td>
                                            <td>{{ $menu['nama'] }}</td>
                                            <td class="text-end">{{ $angka($menu['porsi']) }}</td>
                                            <td class="text-end pe-3">{{ $rupiah($menu['penjualan']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted p-3 mb-0">Belum ada menu terjual bulan ini.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Daftar bahan baku menipis (owner dan kasir) --}}
    <div class="card mb-4" id="daftar-menipis">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Bahan Baku Menipis</span>
            <a href="/barang-masuk/create" class="btn btn-sm btn-primary">Catat Barang Masuk</a>
        </div>
        <div class="card-body p-0">
            @if ($bahanMenipis->isNotEmpty())
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Bahan Baku</th>
                            <th class="text-end">Stok Saat Ini</th>
                            <th class="text-end pe-3">Stok Minimum</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bahanMenipis as $bahan)
                            <tr>
                                <td class="ps-3">{{ $bahan->nama }}</td>
                                <td class="text-end text-danger fw-semibold">{{ $angka($bahan->stok) }} {{ $bahan->satuan }}</td>
                                <td class="text-end pe-3">{{ $angka($bahan->stok_minimum) }} {{ $bahan->satuan }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted p-3 mb-0">Seluruh stok bahan baku masih aman.</p>
            @endif
        </div>
    </div>

    @if ($tampilkanLaba)
        {{-- Chart.js lokal, hanya dimuat untuk owner --}}
        <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
        <script>
            // Data dari DasborService, diubah ke format JSON supaya bisa dibaca JavaScript.
            const dataGrafik = @json($grafik);

            const formatRupiah = (nilai) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(nilai);
            // Format singkat untuk sumbu, contoh 2.100.000 -> "2,1 jt".
            const formatSingkat = (nilai) => new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(nilai);

            new Chart(document.getElementById('grafik-penjualan'), {
                type: 'line',
                data: {
                    labels: dataGrafik.label,
                    datasets: [{
                        label: 'Penjualan',
                        data: dataGrafik.nilai,
                        borderColor: '#2a78d6',
                        backgroundColor: '#2a78d6',
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHitRadius: 12,
                        tension: 0,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        // Satu seri saja: judul kartu sudah menjelaskan, jadi legenda tidak perlu.
                        legend: { display: false },
                        tooltip: { callbacks: { label: (konteks) => formatRupiah(konteks.parsed.y) } },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#6c757d', maxRotation: 0 } },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#eceff1' },
                            border: { display: false },
                            ticks: { color: '#6c757d', callback: (nilai) => formatSingkat(nilai) },
                        },
                    },
                },
            });
        </script>
    @endif
@endsection
