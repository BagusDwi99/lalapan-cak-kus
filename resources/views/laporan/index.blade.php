@extends('layouts.app')

@section('judul', 'Laporan')

@section('konten')
    @php
        // Format tampilan gaya Indonesia (bukan rumus).
        $rupiah = fn ($nilai) => Number::currency($nilai, in: 'IDR', locale: 'id');
        $angka = fn ($nilai) => Number::format($nilai, maxPrecision: 2, locale: 'id');
    @endphp

    {{-- Kop yang hanya muncul saat dicetak --}}
    <div class="d-none d-print-block mb-3">
        <div class="fs-4 fw-semibold">Warung Lalapan Cak Kus</div>
        <div class="small">Ketewel, Sukawati, Gianyar &middot; Dicetak {{ now()->format('d/m/Y H:i') }}</div>
        <hr>
    </div>

    {{-- Tombol Cetak di kanan judul. Mencetak tab yang sedang terbuka; kepala halaman tidak ikut tercetak. --}}
    <x-kepala-halaman class="d-print-none" judul="Laporan" keterangan="Laporan penjualan per menu dan laporan persediaan bahan baku per periode">
        <button type="button" class="btn btn-primary" onclick="window.print()">Cetak</button>
    </x-kepala-halaman>

    {{-- Tab Bootstrap. Tab aktif ditentukan server supaya tetap terbuka setelah menyaring. --}}
    <ul class="nav nav-tabs mb-3 d-print-none" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link @if ($tab === 'penjualan') active @endif" id="tab-penjualan" data-bs-toggle="tab"
                    data-bs-target="#isi-penjualan" type="button" role="tab" aria-controls="isi-penjualan"
                    aria-selected="{{ $tab === 'penjualan' ? 'true' : 'false' }}">Laporan Penjualan</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link @if ($tab === 'persediaan') active @endif" id="tab-persediaan" data-bs-toggle="tab"
                    data-bs-target="#isi-persediaan" type="button" role="tab" aria-controls="isi-persediaan"
                    aria-selected="{{ $tab === 'persediaan' ? 'true' : 'false' }}">Laporan Persediaan</button>
        </li>
    </ul>

    <div class="tab-content">
        {{-- ============ Tab 1: Laporan Penjualan ============ --}}
        <div class="tab-pane fade @if ($tab === 'penjualan') show active @endif" id="isi-penjualan" role="tabpanel" aria-labelledby="tab-penjualan">
            <form method="GET" action="/laporan" class="row g-2 align-items-end jarak-bagian d-print-none">
                <input type="hidden" name="tab" value="penjualan">
                {{-- Rentang tab persediaan ikut dikirim supaya tidak hilang --}}
                <input type="hidden" name="stok_dari" value="{{ $stokDari->toDateString() }}">
                <input type="hidden" name="stok_sampai" value="{{ $stokSampai->toDateString() }}">
                <div class="col-auto">
                    <label for="jual_dari" class="form-label small mb-1">Dari Tanggal</label>
                    <input type="date" id="jual_dari" name="jual_dari" value="{{ $jualDari->toDateString() }}" class="form-control">
                </div>
                <div class="col-auto">
                    <label for="jual_sampai" class="form-label small mb-1">Sampai Tanggal</label>
                    <input type="date" id="jual_sampai" name="jual_sampai" value="{{ $jualSampai->toDateString() }}" class="form-control">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-saring">Tampilkan</button>
                </div>
            </form>

            <h2 class="h5 mb-2">
                Laporan Penjualan per Menu
                <span class="fw-normal text-muted fs-6">Periode {{ $jualDari->format('d/m/Y') }} s.d. {{ $jualSampai->format('d/m/Y') }}</span>
            </h2>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Menu</th>
                                <th class="text-end">Porsi Terjual</th>
                                <th class="text-end">Total Penjualan</th>
                                <th class="text-end">Total HPP</th>
                                <th class="text-end">Total Laba Kotor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($penjualan['baris'] as $baris)
                                <tr class="{{ $baris['porsi'] === 0 ? 'text-muted' : '' }}">
                                    <td>{{ $baris['nama'] }}</td>
                                    <td class="text-end">{{ $angka($baris['porsi']) }}</td>
                                    <td class="text-end">{{ $rupiah($baris['penjualan']) }}</td>
                                    <td class="text-end">{{ $rupiah($baris['hpp']) }}</td>
                                    <td class="text-end">{{ $rupiah($baris['laba']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="sel-kosong">Belum ada data menu.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td>Total</td>
                                <td class="text-end">{{ $angka($penjualan['total']['porsi']) }}</td>
                                <td class="text-end">{{ $rupiah($penjualan['total']['penjualan']) }}</td>
                                <td class="text-end">{{ $rupiah($penjualan['total']['hpp']) }}</td>
                                <td class="text-end">{{ $rupiah($penjualan['total']['laba']) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <p class="small text-muted mt-2">Diurutkan dari total penjualan terbesar. Menu yang tidak terjual pada periode ini ditampilkan abu-abu.</p>
        </div>

        {{-- ============ Tab 2: Laporan Persediaan ============ --}}
        <div class="tab-pane fade @if ($tab === 'persediaan') show active @endif" id="isi-persediaan" role="tabpanel" aria-labelledby="tab-persediaan">
            <form method="GET" action="/laporan" class="row g-2 align-items-end jarak-bagian d-print-none">
                <input type="hidden" name="tab" value="persediaan">
                {{-- Rentang tab penjualan ikut dikirim supaya tidak hilang --}}
                <input type="hidden" name="jual_dari" value="{{ $jualDari->toDateString() }}">
                <input type="hidden" name="jual_sampai" value="{{ $jualSampai->toDateString() }}">
                <div class="col-auto">
                    <label for="stok_dari" class="form-label small mb-1">Dari Tanggal</label>
                    <input type="date" id="stok_dari" name="stok_dari" value="{{ $stokDari->toDateString() }}" class="form-control">
                </div>
                <div class="col-auto">
                    <label for="stok_sampai" class="form-label small mb-1">Sampai Tanggal</label>
                    <input type="date" id="stok_sampai" name="stok_sampai" value="{{ $stokSampai->toDateString() }}" class="form-control">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-saring">Tampilkan</button>
                </div>
            </form>

            <h2 class="h5 mb-2">
                Laporan Persediaan Bahan Baku
                <span class="fw-normal text-muted fs-6">Periode {{ $stokDari->format('d/m/Y') }} s.d. {{ $stokSampai->format('d/m/Y') }}</span>
            </h2>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Bahan Baku</th>
                                <th>Satuan</th>
                                <th class="text-end">Stok Awal</th>
                                <th class="text-end">Masuk</th>
                                <th class="text-end">Terpakai</th>
                                <th class="text-end">Stok Akhir</th>
                                <th class="text-end">Harga Rata-rata</th>
                                <th class="text-end">Nilai Persediaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($persediaan['baris'] as $baris)
                                <tr class="{{ $baris['menipis'] ? 'table-danger' : '' }}">
                                    <td>
                                        {{ $baris['nama'] }}
                                        @if ($baris['menipis'])
                                            <span class="badge bg-danger ms-1">Stok menipis</span>
                                        @endif
                                    </td>
                                    <td>{{ $baris['satuan'] }}</td>
                                    <td class="text-end">{{ $angka($baris['stokAwal']) }}</td>
                                    <td class="text-end">{{ $angka($baris['masuk']) }}</td>
                                    <td class="text-end">{{ $angka($baris['terpakai']) }}</td>
                                    <td class="text-end fw-semibold">{{ $angka($baris['stokAkhir']) }}</td>
                                    <td class="text-end">{{ $rupiah($baris['hargaRataRata']) }}</td>
                                    <td class="text-end">{{ $rupiah($baris['nilai']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="sel-kosong">Belum ada data bahan baku.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="7">Total Nilai Persediaan</td>
                                <td class="text-end">{{ $rupiah($persediaan['totalNilai']) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <p class="small text-muted mt-2">
                Stok akhir = stok awal + masuk &minus; terpakai. Nilai persediaan = stok akhir &times; harga rata-rata saat ini.
                Baris merah: stok akhir periode kurang dari atau sama dengan stok minimum.
            </p>
        </div>
    </div>

    {{-- Pengaturan saat dicetak: sidebar, tombol, dan penyaring disembunyikan (kelas d-print-none),
         hanya tab yang sedang terbuka yang ikut tercetak. --}}
    <style>
        @media print {
            body { background: #fff !important; }
            main { padding: 0 !important; }
            .card { border: none; }
            .table { font-size: 11pt; }
            /* Warna latar baris (penanda stok menipis) tetap ikut tercetak */
            .table-danger, .table-danger > td, .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
@endsection
