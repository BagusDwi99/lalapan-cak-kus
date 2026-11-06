{{--
    Isian formulir yang dipakai bersama oleh halaman tambah dan ubah.
    $bahanBaku->exists bernilai true jika data sudah ada di basis data (mode ubah).
--}}
@php $modeUbah = $bahanBaku->exists; @endphp

<div class="mb-3">
    <label for="nama" class="form-label">Nama</label>
    {{-- old() mengisi ulang nilai yang tadi diketik jika validasi gagal --}}
    <input type="text" id="nama" name="nama" value="{{ old('nama', $bahanBaku->nama) }}"
           class="form-control @error('nama') is-invalid @enderror" autofocus>
    @error('nama')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="satuan" class="form-label">Satuan</label>
    <input type="text" id="satuan" name="satuan" value="{{ old('satuan', $bahanBaku->satuan) }}"
           class="form-control @error('satuan') is-invalid @enderror" placeholder="contoh: kg, ekor, liter, potong">
    @error('satuan')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="stok" class="form-label">{{ $modeUbah ? 'Stok' : 'Stok Awal' }}</label>
    @if ($modeUbah)
        {{-- Mode ubah: hanya ditampilkan. Input disabled tidak ikut terkirim ke server. --}}
        <input type="text" id="stok" class="form-control" disabled
               value="{{ Number::format($bahanBaku->stok, maxPrecision: 3, locale: 'id') }}">
        <div class="form-text">Stok hanya berubah lewat Barang Masuk dan Transaksi Penjualan.</div>
    @else
        <input type="number" id="stok" name="stok" value="{{ old('stok', $bahanBaku->stok + 0) }}"
               step="0.001" min="0" class="form-control @error('stok') is-invalid @enderror">
        @error('stok')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Setelah disimpan, stok hanya berubah lewat Barang Masuk dan Transaksi Penjualan.</div>
    @endif
</div>

<div class="mb-3">
    <label for="stok_minimum" class="form-label">Stok Minimum</label>
    <input type="number" id="stok_minimum" name="stok_minimum" value="{{ old('stok_minimum', $bahanBaku->stok_minimum + 0) }}"
           step="0.001" min="0" class="form-control @error('stok_minimum') is-invalid @enderror">
    @error('stok_minimum')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <div class="form-text">Jika stok sama atau di bawah angka ini, bahan baku ditandai merah di daftar.</div>
</div>

<div class="mb-4">
    <label for="harga_rata_rata" class="form-label">Harga Rata-rata</label>
    {{-- Selalu hanya ditampilkan, tidak pernah dikirim ke server --}}
    <input type="text" id="harga_rata_rata" class="form-control" disabled
           value="{{ Number::currency($bahanBaku->harga_rata_rata, in: 'IDR', locale: 'id') }}">
    <div class="form-text">Dihitung otomatis setiap ada Barang Masuk.</div>
</div>
