@extends('layouts.app')

@section('judul', 'Prediksi Kebutuhan Stok')

@section('konten')
    @php
        // Format angka gaya Indonesia (hanya tampilan, bukan rumus).
        // angka: maksimal 2 angka di belakang koma, contoh 33,92 atau 37.
        // persen: tepat 2 angka di belakang koma, contoh 9,37%. Nilai kosong tampil sebagai tanda pisah.
        $angka = fn ($nilai) => Number::format($nilai, maxPrecision: 2, locale: 'id');
        $persen = fn ($nilai) => $nilai === null ? '–' : Number::format($nilai, precision: 2, locale: 'id') . '%';
    @endphp

    <h1 class="h3 mb-3">Prediksi Kebutuhan Stok</h1>

    {{-- 1. Penyaring --}}
    <form method="POST" action="/prediksi" class="row g-2 align-items-end mb-4">
        @csrf
        <div class="col-md-4">
            <label for="bahan_baku_id" class="form-label small mb-1">Bahan Baku</label>
            <select id="bahan_baku_id" name="bahan_baku_id" class="form-select @error('bahan_baku_id') is-invalid @enderror">
                <option value="">-- Pilih bahan baku --</option>
                @foreach ($daftarBahan as $bahan)
                    <option value="{{ $bahan->id }}" @selected(old('bahan_baku_id', $bahanTerpilih?->id) == $bahan->id)>
                        {{ $bahan->nama }}
                    </option>
                @endforeach
            </select>
            @error('bahan_baku_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-auto">
            {{-- Bukan pilihan: n dikunci 3 untuk SMA dan WMA --}}
            <div class="form-label small mb-1">&nbsp;</div>
            <span class="badge text-bg-light border fs-6 fw-normal py-2">Periode: {{ $periodeN }} hari</span>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Hitung Prediksi</button>
        </div>
    </form>

    @if (! $hasil)
        <div class="alert alert-info">Pilih bahan baku, lalu tekan <strong>Hitung Prediksi</strong>.</div>

    @elseif (! $hasil['cukup'])
        {{-- Data kurang: tampilkan pesan saja, tanpa tabel kosong --}}
        <div class="alert alert-warning">
            <strong>Data belum mencukupi.</strong>
            Pemakaian {{ $bahanTerpilih->nama }} baru tercatat {{ $hasil['jumlahHari'] }} hari,
            sedangkan prediksi membutuhkan minimal {{ $hasil['minimalHari'] }} hari
            ({{ $periodeN }} hari untuk jendela perhitungan dan 1 hari sebagai pembanding akurasi).
            Lanjutkan pencatatan transaksi penjualan, lalu hitung kembali.
        </div>

    @else
        @php
            $satuan = $bahanTerpilih->satuan;
            $metode = $hasil['metodeDipakai'];
            $namaTarget = $hasil['targetBesok'] ? 'Besok' : 'Hari Ini';
        @endphp

        @if (! $hasil['targetBesok'])
            <p class="small text-muted">
                Tutup buku hari ini belum dilakukan, jadi data hari ini belum dipakai dan prediksi dibuat untuk hari ini.
                Setelah tutup buku, prediksi akan dibuat untuk besok.
            </p>
        @endif

        {{-- 2. Tiga kartu ringkasan --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Prediksi Kebutuhan {{ $namaTarget }} ({{ $hasil['tanggalTarget']->format('d/m/Y') }})</div>
                        <div class="display-6 fw-semibold my-1">{{ $angka($hasil['prediksiTerpilih']) }} <span class="fs-5 fw-normal">{{ $satuan }}</span></div>
                        <div class="small text-muted">
                            Memakai varian <strong>{{ $metode }}</strong>
                            {{ $hasil['metodeTerpilih'] ? '(MAPE terkecil)' : '(MAPE tidak dapat dihitung)' }}
                            dari {{ $periodeN }} hari terakhir.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Rekomendasi Pembelian</div>
                        <div class="display-6 fw-semibold my-1">{{ $angka($hasil['rekomendasi']) }} <span class="fs-5 fw-normal">{{ $satuan }}</span></div>
                        <div class="small text-muted">
                            Prediksi {{ $angka($hasil['prediksiTerpilih']) }} &minus; stok tersedia {{ $angka($hasil['stok']) }}
                            = {{ $angka($hasil['selisih']) }} {{ $satuan }}.
                            @if ($hasil['selisih'] <= 0)
                                Hasil tidak positif, jadi rekomendasi 0: stok saat ini masih mencukupi.
                            @else
                                Beli minimal sebanyak selisih ini.
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Akurasi (MAPE {{ $metode }})</div>
                        <div class="display-6 fw-semibold my-1">{{ $persen($hasil['mapeTerpilih']) }}</div>
                        <div class="small">
                            @if ($hasil['kategori'])
                                Kategori: <strong>{{ $hasil['kategori'] }}</strong>
                                <span class="text-muted">(kriteria Lewis)</span>
                            @else
                                <span class="text-muted">Tidak dapat dihitung karena seluruh pemakaian aktual bernilai nol.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Tabel perbandingan. Kolom varian terpilih diberi latar berbeda. --}}
        @php
            $latarSma = $hasil['metodeTerpilih'] === 'SMA' ? 'table-primary' : '';
            $latarWma = $hasil['metodeTerpilih'] === 'WMA' ? 'table-primary' : '';
        @endphp
        <div class="card mb-3">
            <div class="card-header fw-semibold">
                Perbandingan SMA dan WMA &mdash; {{ $bahanTerpilih->nama }} ({{ $satuan }})
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th class="text-end">Pemakaian Aktual</th>
                            <th class="text-end {{ $latarSma }}">Prediksi SMA</th>
                            <th class="text-end {{ $latarSma }}">APE SMA</th>
                            <th class="text-end {{ $latarWma }}">Prediksi WMA</th>
                            <th class="text-end {{ $latarWma }}">APE WMA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hasil['baris'] as $baris)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($baris['tanggal'])->format('d/m/Y') }}</td>
                                <td class="text-end">{{ $angka($baris['aktual']) }}</td>
                                <td class="text-end {{ $latarSma }}">{{ $angka($baris['sma']) }}</td>
                                <td class="text-end {{ $latarSma }}">{{ $persen($baris['apeSma']) }}</td>
                                <td class="text-end {{ $latarWma }}">{{ $angka($baris['wma']) }}</td>
                                <td class="text-end {{ $latarWma }}">{{ $persen($baris['apeWma']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="2">MAPE</td>
                            <td class="{{ $latarSma }}"></td>
                            <td class="text-end {{ $latarSma }}">{{ $persen($hasil['mapeSma']) }}</td>
                            <td class="{{ $latarWma }}"></td>
                            <td class="text-end {{ $latarWma }}">{{ $persen($hasil['mapeWma']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer small text-muted">
                Prediksi tiap tanggal dihitung dari {{ $periodeN }} hari sebelumnya.
                Bobot WMA: 0,5 untuk satu hari sebelumnya, 0,3 untuk dua hari sebelumnya, 0,2 untuk tiga hari sebelumnya.
                @if ($hasil['metodeTerpilih'])
                    Kolom berlatar biru adalah varian terpilih ({{ $hasil['metodeTerpilih'] }}).
                @endif
                Tanda &ndash; pada APE berarti pemakaian aktual hari itu nol, sehingga tidak ikut dihitung dalam MAPE.
            </div>
        </div>

        {{-- 4. Kotak kesimpulan --}}
        <div class="alert alert-secondary mb-4">
            <strong>Kesimpulan:</strong>
            @if ($hasil['metodeTerpilih'])
                MAPE SMA sebesar {{ $persen($hasil['mapeSma']) }} dan MAPE WMA sebesar {{ $persen($hasil['mapeWma']) }}.
                @if ($hasil['mapeSma'] == $hasil['mapeWma'])
                    MAPE kedua varian sama besar, sehingga
                @else
                    MAPE {{ $hasil['metodeTerpilih'] }} lebih kecil, sehingga
                @endif
                varian yang direkomendasikan untuk {{ $bahanTerpilih->nama }} adalah
                <strong>{{ $hasil['metodeTerpilih'] }}</strong>, dengan kategori akurasi
                <strong>{{ $hasil['kategori'] }}</strong> menurut kriteria Lewis.
            @else
                MAPE tidak dapat dihitung karena seluruh pemakaian aktual {{ $bahanTerpilih->nama }} pada periode
                evaluasi bernilai nol. Prediksi sementara memakai SMA.
            @endif
        </div>

        {{-- 5. Grafik --}}
        <div class="card mb-4">
            <div class="card-header fw-semibold">Grafik Pemakaian Aktual dan Prediksi &mdash; {{ $bahanTerpilih->nama }}</div>
            <div class="card-body">
                <div style="position: relative; height: 340px;">
                    <canvas id="grafik-prediksi" aria-label="Grafik garis pemakaian aktual, prediksi SMA, dan prediksi WMA per hari" role="img"></canvas>
                </div>
                <p class="small text-muted mb-0 mt-2">
                    Garis putus-putus adalah prediksi. Titik terakhir ({{ $hasil['tanggalTarget']->format('d/m') }}) adalah
                    prediksi {{ strtolower($namaTarget) }}, belum ada pemakaian aktualnya.
                </p>
            </div>
        </div>

        {{-- Chart.js disimpan lokal di public/vendor/chartjs --}}
        <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
        <script>
            // Data dari server (PrediksiService), diubah ke format JSON supaya bisa dibaca JavaScript.
            const dataGrafik = @json($hasil['grafik']);
            const satuan = @json($satuan);

            // Format angka gaya Indonesia untuk sumbu dan tooltip, contoh 33,92.
            const formatAngka = (nilai) => nilai.toLocaleString('id-ID', { maximumFractionDigits: 2 });

            // Warna seri: tiga slot pertama palet kategorikal yang sudah divalidasi
            // (aman untuk buta warna). Prediksi dibedakan juga dengan pola garis putus-putus.
            const warna = { aktual: '#2a78d6', sma: '#eb6834', wma: '#1baf7a' };
            const warnaTeks = '#212529';
            const warnaTeksSekunder = '#6c757d';

            // Pengaturan garis: tebal 2px, titik disembunyikan dan muncul saat disorot.
            const garis = (label, data, warnaGaris, polaPutus) => ({
                label: label,
                data: data,
                borderColor: warnaGaris,
                backgroundColor: warnaGaris,
                borderWidth: 2,
                borderDash: polaPutus,
                pointRadius: 0,
                pointHoverRadius: 5,
                pointHitRadius: 12,
                tension: 0,
                spanGaps: false,
            });

            new Chart(document.getElementById('grafik-prediksi'), {
                type: 'line',
                data: {
                    labels: dataGrafik.label,
                    datasets: [
                        garis('Pemakaian aktual', dataGrafik.aktual, warna.aktual, []),
                        garis('Prediksi SMA', dataGrafik.sma, warna.sma, [6, 4]),
                        garis('Prediksi WMA', dataGrafik.wma, warna.wma, [2, 3]),
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    // Sorot satu tanggal: tooltip memuat ketiga seri sekaligus.
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'start',
                            labels: { color: warnaTeks, boxWidth: 28, boxHeight: 2, useBorderRadius: false },
                        },
                        tooltip: {
                            // Lewati seri yang kosong di tanggal itu (misalnya aktual pada titik prediksi terakhir).
                            filter: (item) => item.parsed.y !== null,
                            callbacks: {
                                label: (konteks) => `${konteks.dataset.label}: ${formatAngka(konteks.parsed.y)} ${satuan}`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: warnaTeksSekunder, maxRotation: 0, autoSkipPadding: 12 },
                        },
                        y: {
                            grace: '5%',
                            grid: { color: '#eceff1' },
                            border: { display: false },
                            ticks: { color: warnaTeksSekunder, callback: (nilai) => formatAngka(nilai) },
                            title: { display: true, text: `Pemakaian (${satuan})`, color: warnaTeksSekunder },
                        },
                    },
                },
            });
        </script>
    @endif
@endsection
