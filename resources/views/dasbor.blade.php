{{-- Halaman dasbor memakai kerangka layouts/app.blade.php --}}
@extends('layouts.app')

@section('judul', 'Dasbor')

@section('konten')
    @php
        // Format tampilan gaya Indonesia (bukan rumus).
        $rupiah = fn ($nilai) => Number::currency($nilai, in: 'IDR', locale: 'id');
        $angka = fn ($nilai) => Number::format($nilai, maxPrecision: 2, locale: 'id');
    @endphp

    <div class="jarak-bagian">
        <h1 class="h3 mb-1">Dasbor</h1>
        <div class="text-body-secondary">
            Selamat datang, {{ auth()->user()->name }}.
            Hari ini {{ today()->locale('id')->translatedFormat('l, j F Y') }}.
        </div>
    </div>

    {{-- Status tutup buku hari ini (owner dan kasir) --}}
    @if ($tutupBukuHariIni)
        <div class="alert alert-success d-flex align-items-center justify-content-between">
            <span><strong>Sudah tutup buku.</strong> Hari ini ditutup pada pukul {{ $tutupBukuHariIni->created_at->format('H:i') }}.</span>
            <a href="/tutup-buku" class="btn btn-sm btn-outline-primary bg-white">Lihat Tutup Buku</a>
        </div>
    @else
        {{-- Warna kalem alert-warning diatur di partials/gaya (sama di seluruh halaman) --}}
        <div class="alert alert-warning d-flex align-items-center justify-content-between">
            <span><strong>Belum tutup buku.</strong> Lakukan tutup buku di akhir hari operasional.</span>
            <a href="/tutup-buku" class="btn btn-sm btn-outline-primary bg-white">Ke Tutup Buku</a>
        </div>
    @endif

    {{-- Kartu ringkasan hari ini. Owner: 4 kartu. Kasir: 3 kartu, tanpa laba.
         Label dan angka rata kiri. Angka tepat di bawah label (mt-2), sehingga posisinya
         sejajar di semua kartu. h-100: semua kartu sama tinggi. --}}
    @php $kolomKartu = $tampilkanLaba ? 'col-sm-6 col-xl-3' : 'col-md-4'; @endphp
    <div class="row g-bagian jarak-bagian">
        @if ($tampilkanLaba)
            <div class="{{ $kolomKartu }}">
                <div class="card h-100"><div class="card-body">
                    <div class="label-kartu">Penjualan Hari Ini</div>
                    <div class="angka-besar mt-2">{{ $rupiah($penjualanHariIni) }}</div>
                </div></div>
            </div>
            <div class="{{ $kolomKartu }}">
                <div class="card h-100"><div class="card-body">
                    <div class="label-kartu">Laba Kotor Hari Ini</div>
                    <div class="angka-besar mt-2">{{ $rupiah($labaHariIni) }}</div>
                </div></div>
            </div>
            <div class="{{ $kolomKartu }}">
                <div class="card h-100"><div class="card-body">
                    <div class="label-kartu">Transaksi Hari Ini</div>
                    <div class="angka-besar mt-2">{{ $jumlahTransaksiHariIni }} <span class="fs-6 fw-normal">nota</span></div>
                </div></div>
            </div>
        @else
            <div class="{{ $kolomKartu }}">
                <div class="card h-100"><div class="card-body">
                    <div class="label-kartu">Transaksi Hari Ini</div>
                    <div class="angka-besar mt-2">{{ $jumlahTransaksiHariIni }} <span class="fs-6 fw-normal">nota</span></div>
                </div></div>
            </div>
            <div class="{{ $kolomKartu }}">
                <div class="card h-100"><div class="card-body">
                    <div class="label-kartu">Penjualan Hari Ini</div>
                    <div class="angka-besar mt-2">{{ $rupiah($penjualanHariIni) }}</div>
                </div></div>
            </div>
        @endif

        {{-- Kartu bahan menipis (owner dan kasir). Tautan "Lihat daftar" di bawah angka. --}}
        <div class="{{ $kolomKartu }}">
            <div class="card h-100 {{ $jumlahMenipis > 0 ? 'border-danger' : '' }}"><div class="card-body">
                <div class="label-kartu">Bahan Baku Menipis</div>
                <div class="angka-besar mt-2 {{ $jumlahMenipis > 0 ? 'text-danger' : '' }}">
                    {{ $jumlahMenipis }} <span class="fs-6 fw-normal">bahan</span>
                </div>
                <a href="#daftar-menipis" class="small d-inline-block mt-1">Lihat daftar</a>
            </div></div>
        </div>
    </div>

    @if ($tampilkanLaba)
        @php $namaBulan = today()->locale('id')->translatedFormat('F Y'); @endphp

        <div class="row g-bagian jarak-bagian">
            {{-- Grafik penjualan 14 hari terakhir (owner) --}}
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header fw-semibold">Penjualan Harian 14 Hari Terakhir</div>
                    <div class="card-body">
                        <div style="position: relative; height: 260px;">
                            <canvas id="grafik-penjualan" role="img"
                                    aria-label="Grafik garis total penjualan per hari selama 14 hari terakhir"></canvas>
                        </div>
                        <div class="small text-body-secondary mt-2">Titik terakhir adalah hari ini, yang masih berjalan.</div>
                    </div>
                </div>
            </div>

            {{-- Ringkasan bulan berjalan (owner). Angka rata kanan supaya digitnya sejajar. --}}
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Ringkasan {{ $namaBulan }}</span>
                        <a href="/laba-rugi" class="small">Lihat Laba Rugi</a>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="ps-0 text-body-secondary">Penjualan</td>
                                    <td class="pe-0 text-end fw-semibold">{{ $rupiah($bulanIni['totalPenjualan']) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-0 text-body-secondary">Laba Kotor</td>
                                    <td class="pe-0 text-end fw-semibold">{{ $rupiah($bulanIni['labaKotor']) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-0 text-body-secondary">Biaya Operasional</td>
                                    <td class="pe-0 text-end fw-semibold">{{ $rupiah($bulanIni['totalBiaya']) }}</td>
                                </tr>
                                {{-- Laba bersih negatif (rugi) ditampilkan merah --}}
                                {{-- pb-0: tanpa padding bawah, supaya jarak ke keterangan tepat 16px --}}
                                <tr class="border-top">
                                    <td class="ps-0 pt-3 pb-0 fw-semibold">{{ $bulanIni['labaBersih'] < 0 ? 'Rugi Bersih' : 'Laba Bersih' }}</td>
                                    <td class="pe-0 pt-3 pb-0 text-end fs-5 fw-bold {{ $bulanIni['labaBersih'] < 0 ? 'text-danger' : '' }}">
                                        {{ $rupiah($bulanIni['labaBersih']) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        {{-- mt-3 = 16px tepat di bawah baris laba/rugi bersih; sisa ruang kartu dibiarkan kosong --}}
                        <div class="small text-body-secondary mt-3">
                            Biaya operasional dihitung untuk satu bulan penuh, sedangkan penjualan baru sampai hari ini.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-bagian jarak-bagian">
        @if ($tampilkanLaba)
            {{-- Lima menu terlaris bulan ini (owner) --}}
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header fw-semibold">Menu Terlaris {{ $namaBulan }}</div>
                    @if (count($menuTerlaris) > 0)
                        <table class="table tabel-kartu align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>Menu</th>
                                    <th class="text-end">Porsi</th>
                                    <th class="text-end">Penjualan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($menuTerlaris as $urutan => $menu)
                                    <tr>
                                        <td class="text-body-secondary">{{ $urutan + 1 }}</td>
                                        <td>{{ $menu['nama'] }}</td>
                                        <td class="text-end">{{ $angka($menu['porsi']) }}</td>
                                        <td class="text-end">{{ $rupiah($menu['penjualan']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="card-body text-body-secondary">Belum ada menu terjual bulan ini.</div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Daftar bahan baku menipis (owner dan kasir) --}}
        <div class="{{ $tampilkanLaba ? 'col-lg-6' : 'col-12' }}">
            <div class="card h-100" id="daftar-menipis">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Bahan Baku Menipis</span>
                    <a href="/barang-masuk/create" class="btn btn-sm btn-primary">Catat Barang Masuk</a>
                </div>
                @if ($bahanMenipis->isNotEmpty())
                    <table class="table tabel-kartu align-middle">
                        <thead>
                            <tr>
                                <th>Bahan Baku</th>
                                <th class="text-end">Stok Saat Ini</th>
                                <th class="text-end">Stok Minimum</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bahanMenipis as $bahan)
                                <tr>
                                    <td>{{ $bahan->nama }}</td>
                                    <td class="text-end text-danger fw-semibold">{{ $angka($bahan->stok) }} {{ $bahan->satuan }}</td>
                                    <td class="text-end">{{ $angka($bahan->stok_minimum) }} {{ $bahan->satuan }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="card-body text-body-secondary">Seluruh stok bahan baku masih aman.</div>
                @endif
            </div>
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

            // Warna diambil dari variabel CSS di layout, supaya sama dengan palet aplikasi.
            const gaya = getComputedStyle(document.documentElement);
            const warnaPrimary = gaya.getPropertyValue('--primary').trim();
            const warnaTeksMuted = gaya.getPropertyValue('--teks-muted').trim();

            // Gambar grafik setelah huruf Inter selesai dimuat, supaya teks sumbu memakai Inter.
            document.fonts.ready.then(function () {
                Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
                Chart.defaults.font.size = 12;
                Chart.defaults.color = warnaTeksMuted;

                new Chart(document.getElementById('grafik-penjualan'), {
                    type: 'line',
                    data: {
                        labels: dataGrafik.label,
                        datasets: [{
                            label: 'Penjualan',
                            data: dataGrafik.nilai,
                            borderColor: warnaPrimary,
                            backgroundColor: warnaPrimary,
                            borderWidth: 2,
                            pointRadius: 2.5,
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
                            // Tanpa garis bantu vertikal.
                            x: { grid: { display: false }, border: { color: '#DDE3EC' }, ticks: { maxRotation: 0 } },
                            // Garis bantu horizontal tipis.
                            y: {
                                beginAtZero: true,
                                grid: { color: '#EDF0F5' },
                                border: { display: false },
                                ticks: { callback: (nilai) => formatSingkat(nilai) },
                            },
                        },
                    },
                });
            });
        </script>
    @endif
@endsection
