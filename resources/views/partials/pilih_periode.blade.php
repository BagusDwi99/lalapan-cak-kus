{{--
    Dua pilihan periode: bulan dan tahun. Dipakai di formulir biaya operasional,
    penyaring biaya operasional, dan halaman laba rugi.

    Variabel:
    $bulanTerpilih  = angka bulan yang terpilih (1-12) atau null
    $tahunTerpilih  = tahun yang terpilih atau null
    $pilihanSemua   = true jika ada pilihan "Semua" (untuk penyaring)
--}}
@php
    $pilihanSemua = $pilihanSemua ?? false;

    // Tahun yang bisa dipilih: 3 tahun ke belakang sampai tahun depan.
    // Tahun yang sedang terpilih selalu ikut, walaupun di luar rentang itu.
    $daftarTahun = range(now()->year - 3, now()->year + 1);
    if ($tahunTerpilih && ! in_array((int) $tahunTerpilih, $daftarTahun)) {
        $daftarTahun[] = (int) $tahunTerpilih;
        sort($daftarTahun);
    }
@endphp

<div class="col-auto">
    <label for="bulan" class="form-label small mb-1">Bulan</label>
    <select id="bulan" name="bulan" class="form-select @error('bulan') is-invalid @enderror">
        @if ($pilihanSemua)
            <option value="">Semua bulan</option>
        @endif
        @for ($b = 1; $b <= 12; $b++)
            {{-- Nama bulan dalam bahasa Indonesia, contoh: Januari, Februari --}}
            <option value="{{ $b }}" @selected((int) $bulanTerpilih === $b)>
                {{ \Illuminate\Support\Carbon::create(2000, $b, 1)->locale('id')->translatedFormat('F') }}
            </option>
        @endfor
    </select>
    @error('bulan')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-auto">
    <label for="tahun" class="form-label small mb-1">Tahun</label>
    <select id="tahun" name="tahun" class="form-select @error('tahun') is-invalid @enderror">
        @if ($pilihanSemua)
            <option value="">Semua tahun</option>
        @endif
        @foreach ($daftarTahun as $t)
            <option value="{{ $t }}" @selected((int) $tahunTerpilih === $t)>{{ $t }}</option>
        @endforeach
    </select>
    @error('tahun')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
