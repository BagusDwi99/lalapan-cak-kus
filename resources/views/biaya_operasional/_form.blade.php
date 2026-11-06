{{-- Isian formulir yang dipakai bersama oleh halaman tambah dan ubah biaya operasional. --}}
<div class="mb-3">
    <label for="nama" class="form-label">Nama Biaya</label>
    <input type="text" id="nama" name="nama" value="{{ old('nama', $biaya->nama) }}"
           class="form-control @error('nama') is-invalid @enderror" placeholder="contoh: Listrik, Gas LPG, Gaji Kasir" autofocus>
    @error('nama')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="jumlah" class="form-label">Jumlah (Rp)</label>
    <input type="number" id="jumlah" name="jumlah" step="0.01" min="0"
           value="{{ old('jumlah', $biaya->exists ? $biaya->jumlah + 0 : '') }}"
           class="form-control @error('jumlah') is-invalid @enderror">
    @error('jumlah')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row g-2 mb-4">
    @include('partials.pilih_periode', [
        'bulanTerpilih' => old('bulan', $biaya->periode?->month),
        'tahunTerpilih' => old('tahun', $biaya->periode?->year),
    ])
</div>
