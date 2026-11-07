@extends('layouts.app')

@section('judul', 'Prediksi Kebutuhan Stok')

@section('konten')
    @php
        // Format angka gaya Indonesia (hanya tampilan, bukan rumus).
        // angka: maksimal 2 angka di belakang koma, contoh 33,92 atau 37.
        // persen: tepat 2 angka di belakang koma, contoh 9,37%. Nilai kosong tampil sebagai tanda pisah.
        $angka = fn ($nilai) => Number::format($nilai, maxPrecision: 2, locale: 'id');
        $persen = fn ($nilai) => $nilai === null ? '–' : Number::format($nilai, precision: 2, locale: 'id') . '%';

        // Rentang tiap kategori kriteria Lewis, untuk keterangan di kartu akurasi (hanya teks).
        $rentangLewis = [
            'Sangat akurat' => 'MAPE < 10%',
            'Akurat' => '10% ≤ MAPE < 20%',
            'Wajar' => '20% ≤ MAPE < 50%',
            'Tidak akurat' => 'MAPE ≥ 50%',
        ];
    @endphp

    {{-- Warna khusus halaman ini: satu warna untuk tiap varian peramalan --}}
    <style>
        :root {
            /* Warna garis grafik, dipakai juga oleh keterangan garis supaya selalu sama */
            --garis-aktual: #1B2230;
            --garis-sma: #1D4ED8;
            --garis-wma: #C2410C;

            /* Warna tabel per varian */
            --sma: #1F4E79;  --sma-bg: #EDF2F8;  --sma-bg-lembut: #FAFCFE;
            --wma: #8A4B10;  --wma-bg: #FBF2E8;  --wma-bg-lembut: #FEFCF9;
        }

        /* Keterangan periode tetap, tingginya disamakan dengan dropdown */
        .periode-tetap {
            display: inline-flex; align-items: center; height: 38px; padding: 0 12px;
            background: #fff; border: 1px solid var(--card-border); border-radius: 6px; color: var(--teks-muted);
        }

        /* Angka besar kartu prediksi 34px, satuannya ukuran biasa */
        .angka-prediksi { font-size: 34px; font-weight: 700; line-height: 1.15; letter-spacing: -.01em; }
        .angka-prediksi .satuan { font-size: 14px; font-weight: 400; color: var(--teks-muted); margin-left: 4px; letter-spacing: 0; }

        /* Kolom tabel per varian: judul berlatar warna varian, sel data berlatar lembut */
        .tabel-kartu thead th.kolom-sma { background: var(--sma-bg); color: var(--sma); }
        .tabel-kartu thead th.kolom-wma { background: var(--wma-bg); color: var(--wma); }
        .tabel-kartu td.sel-sma { background-color: var(--sma-bg-lembut); }
        .tabel-kartu td.sel-wma { background-color: var(--wma-bg-lembut); }
        .tabel-kartu tfoot .baris-mape td { border-top: 2px solid #AEB9C8; border-bottom: 0; font-weight: 700; }
        .teks-sma { color: var(--sma); }
        .teks-wma { color: var(--wma); }

        /* Label kecil "Terpilih" di samping MAPE varian pemenang */
        .label-terpilih {
            display: inline-block; margin-right: 8px; padding: 2px 8px; border-radius: 999px;
            font-size: 11px; font-weight: 600; letter-spacing: .02em; color: #fff; vertical-align: 1px;
        }
        .label-terpilih.sma { background: var(--sma); }
        .label-terpilih.wma { background: var(--wma); }

        /* Kotak kesimpulan */
        .kotak-kesimpulan {
            background: var(--sma-bg); border-left: 3px solid var(--primary);
            border-radius: 6px; padding: 14px 18px;
        }

        /* Keterangan garis grafik */
        .legenda-grafik { display: flex; flex-wrap: wrap; gap: 16px; font-size: 12px; color: var(--teks-muted); font-weight: 400; }
        .legenda-item { display: inline-flex; align-items: center; gap: 6px; }
    </style>

    {{-- 1. Kepala halaman: judul dan keterangan di kiri, penyaring di kanan --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 jarak-bagian">
        <div>
            <h1 class="h3 mb-1">Prediksi Kebutuhan Stok</h1>
            <div class="small" style="color: var(--teks-muted);">
                Perbandingan Single Moving Average dan Weighted Moving Average berdasarkan data pemakaian historis
            </div>
        </div>

        <form method="POST" action="/prediksi" class="d-flex flex-wrap align-items-start gap-2">
            @csrf
            <div style="min-width: 220px;">
                <label for="bahan_baku_id" class="visually-hidden">Bahan Baku</label>
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
            {{-- Bukan pilihan: n dikunci 3 untuk SMA dan WMA --}}
            <span class="periode-tetap">Periode: {{ $periodeN }} hari</span>
            <button type="submit" class="btn btn-primary">Hitung Prediksi</button>
        </form>
    </div>

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
            <p class="small" style="color: var(--teks-muted);">
                Tutup buku hari ini belum dilakukan, jadi data hari ini belum dipakai dan prediksi dibuat untuk hari ini.
                Setelah tutup buku, prediksi akan dibuat untuk besok.
            </p>
        @endif

        {{-- 2. Tiga kartu ringkasan, lebar sama, rata kiri --}}
        <div class="row g-bagian jarak-bagian">
            <div class="col-md-4">
                <div class="card h-100"><div class="card-body">
                    <div class="label-kartu">Prediksi Kebutuhan {{ $namaTarget }}</div>
                    <div class="angka-prediksi mt-2">{{ $angka($hasil['prediksiTerpilih']) }}<span class="satuan">{{ $satuan }}</span></div>
                    <div class="small mt-2" style="color: var(--teks-muted);">
                        Varian {{ $metode }}
                        {{ $hasil['metodeTerpilih'] ? '(MAPE terkecil)' : '(MAPE tidak dapat dihitung)' }}
                        untuk {{ $hasil['tanggalTarget']->format('d/m/Y') }}
                    </div>
                </div></div>
            </div>

            <div class="col-md-4">
                <div class="card h-100"><div class="card-body">
                    <div class="label-kartu">Rekomendasi Pembelian</div>
                    <div class="angka-prediksi mt-2" style="color: var(--primary);">{{ $angka($hasil['rekomendasi']) }}<span class="satuan">{{ $satuan }}</span></div>
                    <div class="small mt-2" style="color: var(--teks-muted);">
                        Prediksi {{ $angka($hasil['prediksiTerpilih']) }} &minus; stok tersedia {{ $angka($hasil['stok']) }} {{ $satuan }}
                        @if ($hasil['selisih'] <= 0)
                            <br>Stok masih mencukupi, tidak perlu membeli.
                        @endif
                    </div>
                </div></div>
            </div>

            <div class="col-md-4">
                <div class="card h-100"><div class="card-body">
                    <div class="label-kartu">Akurasi (MAPE {{ $metode }})</div>
                    <div class="angka-prediksi mt-2">{{ $persen($hasil['mapeTerpilih']) }}</div>
                    <div class="small mt-2" style="color: var(--teks-muted);">
                        @if ($hasil['kategori'])
                            Kategori <strong style="color: var(--teks);">{{ $hasil['kategori'] }}</strong>
                            ({{ $rentangLewis[$hasil['kategori']] }}) menurut kriteria Lewis
                        @else
                            Tidak dapat dihitung karena seluruh pemakaian aktual bernilai nol.
                        @endif
                    </div>
                </div></div>
            </div>
        </div>

        {{-- 3. Tabel perbandingan. Kolom SMA dan WMA masing-masing memakai warna variannya. --}}
        <div class="card jarak-bagian">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="fw-semibold">Perbandingan SMA dan WMA</span>
                <span class="small" style="color: var(--teks-muted);">
                    {{ $bahanTerpilih->nama }} ({{ $satuan }}) &middot; n = {{ $periodeN }} &middot; {{ count($hasil['baris']) }} periode pengujian
                </span>
            </div>
            <div class="table-responsive">
                <table class="table tabel-kartu align-middle">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th class="text-end">Pemakaian Aktual</th>
                            <th class="text-end kolom-sma">Prediksi SMA</th>
                            <th class="text-end kolom-sma" title="Absolute Percentage Error — kesalahan prediksi hari itu dalam persen">APE SMA</th>
                            <th class="text-end kolom-wma">Prediksi WMA</th>
                            <th class="text-end kolom-wma" title="Absolute Percentage Error — kesalahan prediksi hari itu dalam persen">APE WMA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hasil['baris'] as $baris)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($baris['tanggal'])->format('d/m/Y') }}</td>
                                <td class="text-end">{{ $angka($baris['aktual']) }}</td>
                                <td class="text-end sel-sma">{{ $angka($baris['sma']) }}</td>
                                <td class="text-end sel-sma">{{ $persen($baris['apeSma']) }}</td>
                                <td class="text-end sel-wma">{{ $angka($baris['wma']) }}</td>
                                <td class="text-end sel-wma">{{ $persen($baris['apeWma']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="baris-mape">
                            <td colspan="2">MAPE (rata-rata APE)</td>
                            <td class="sel-sma"></td>
                            <td class="text-end sel-sma teks-sma">
                                {{-- Label "Terpilih" hanya pada varian dengan MAPE terkecil --}}
                                @if ($hasil['metodeTerpilih'] === 'SMA')
                                    <span class="label-terpilih sma">Terpilih</span>
                                @endif
                                {{ $persen($hasil['mapeSma']) }}
                            </td>
                            <td class="sel-wma"></td>
                            <td class="text-end sel-wma teks-wma">
                                @if ($hasil['metodeTerpilih'] === 'WMA')
                                    <span class="label-terpilih wma">Terpilih</span>
                                @endif
                                {{ $persen($hasil['mapeWma']) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer small" style="color: var(--teks-muted);">
                APE = Absolute Percentage Error per periode. MAPE = rata-rata APE seluruh periode pengujian.<br>
                Bobot WMA: 0,5 untuk satu hari sebelumnya, 0,3 untuk dua hari sebelumnya, 0,2 untuk tiga hari sebelumnya.
                Tanda &ndash; pada APE berarti pemakaian aktual hari itu nol, sehingga tidak ikut dihitung dalam MAPE.
            </div>
        </div>

        {{-- 4. Kotak kesimpulan --}}
        <div class="kotak-kesimpulan jarak-bagian">
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

        {{-- 5. Grafik. Keterangan garis di kanan judul; pola garis berbeda supaya terbaca saat dicetak hitam putih. --}}
        <div class="card jarak-bagian">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="fw-semibold">Grafik Pemakaian Aktual dan Prediksi &mdash; {{ $bahanTerpilih->nama }}</span>
                <div class="legenda-grafik">
                    {{-- Warna dan pola sama persis dengan garis grafik (variabel --garis-*) --}}
                    <span class="legenda-item">
                        <svg width="28" height="10" aria-hidden="true">
                            <line x1="0" y1="5" x2="28" y2="5" style="stroke: var(--garis-aktual); stroke-width: 2.5;"/>
                            <circle cx="14" cy="5" r="2.5" style="fill: var(--garis-aktual);"/>
                        </svg>
                        Pemakaian aktual
                    </span>
                    <span class="legenda-item">
                        <svg width="28" height="10" aria-hidden="true"><line x1="0" y1="5" x2="28" y2="5" style="stroke: var(--garis-sma); stroke-width: 2.5; stroke-dasharray: 8 4;"/></svg>
                        Prediksi SMA
                    </span>
                    <span class="legenda-item">
                        <svg width="28" height="10" aria-hidden="true"><line x1="0" y1="5" x2="28" y2="5" style="stroke: var(--garis-wma); stroke-width: 2.5; stroke-dasharray: 2 4;"/></svg>
                        Prediksi WMA
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 340px;">
                    <canvas id="grafik-prediksi" aria-label="Grafik garis pemakaian aktual, prediksi SMA, dan prediksi WMA per hari" role="img"></canvas>
                </div>
                <div class="small mt-2" style="color: var(--teks-muted);">
                    Titik terakhir ({{ $hasil['tanggalTarget']->format('d/m') }}) adalah prediksi {{ strtolower($namaTarget) }},
                    belum ada pemakaian aktualnya.
                </div>
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

            // Warna diambil dari variabel CSS --garis-*, supaya sama dengan keterangan garis.
            const gaya = getComputedStyle(document.documentElement);
            const warna = {
                aktual: gaya.getPropertyValue('--garis-aktual').trim(),
                sma: gaya.getPropertyValue('--garis-sma').trim(),
                wma: gaya.getPropertyValue('--garis-wma').trim(),
                teksMuted: gaya.getPropertyValue('--teks-muted').trim(),
            };

            // Nilai terkecil dari ketiga seri (titik kosong diabaikan), untuk batas bawah sumbu.
            const nilaiTerkecil = Math.min(...[...dataGrafik.aktual, ...dataGrafik.sma, ...dataGrafik.wma].filter((v) => v !== null));

            // Pengaturan garis, semuanya tebal 2,5px:
            //   aktual: garis utuh dengan titik data bulat kecil,
            //   SMA: putus-putus panjang (garis 8, spasi 4), WMA: titik-titik (garis 2, spasi 4).
            // Garis prediksi tanpa titik data; titik muncul saat disorot.
            const garis = (label, data, warnaGaris, polaPutus, jariJariTitik) => ({
                label: label,
                data: data,
                borderColor: warnaGaris,
                backgroundColor: warnaGaris,
                borderWidth: 2.5,
                borderDash: polaPutus,
                pointRadius: jariJariTitik,
                pointStyle: 'circle',
                pointHoverRadius: 5,
                pointHitRadius: 12,
                tension: 0,
                spanGaps: false,
            });

            // Gambar grafik setelah huruf Inter selesai dimuat, supaya teks sumbu memakai Inter.
            document.fonts.ready.then(function () {
                Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
                Chart.defaults.font.size = 12;
                Chart.defaults.color = warna.teksMuted;

                new Chart(document.getElementById('grafik-prediksi'), {
                    type: 'line',
                    data: {
                        labels: dataGrafik.label,
                        datasets: [
                            garis('Pemakaian aktual', dataGrafik.aktual, warna.aktual, [], 2.5),
                            garis('Prediksi SMA', dataGrafik.sma, warna.sma, [8, 4], 0),
                            garis('Prediksi WMA', dataGrafik.wma, warna.wma, [2, 4], 0),
                        ],
                    },
                    options: {
                        maintainAspectRatio: false,
                        // Sorot satu tanggal: tooltip memuat ketiga seri sekaligus.
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            // Keterangan garis sudah ditampilkan di judul kartu.
                            legend: { display: false },
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
                                border: { color: '#DDE3EC' },
                                ticks: { maxRotation: 0, autoSkipPadding: 12 },
                            },
                            y: {
                                grace: '5%',
                                // Pemakaian tidak mungkin negatif. Jika ada nilai 0, sumbu dimulai tepat dari 0
                                // (tanpa ini, ruang tambahan 5% membuat sumbu turun ke angka minus).
                                min: nilaiTerkecil <= 0 ? 0 : undefined,
                                grid: { color: '#EDF0F5' },
                                border: { display: false },
                                ticks: { callback: (nilai) => formatAngka(nilai) },
                                title: { display: true, text: `Pemakaian (${satuan})` },
                            },
                        },
                    },
                });
            });
        </script>
    @endif
@endsection
