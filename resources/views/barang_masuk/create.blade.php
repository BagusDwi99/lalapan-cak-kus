@extends('layouts.app')

@section('judul', 'Catat Barang Masuk')

@section('konten')
    <x-kepala-halaman judul="Catat Barang Masuk" keterangan="Stok dan harga rata-rata bahan baku diperbarui otomatis saat disimpan" />

    <div class="card kartu-form">
        <div class="card-body">
            <form method="POST" action="/barang-masuk">
                @csrf

                <div class="mb-3">
                    <label for="bahan_baku_id" class="form-label">Bahan Baku</label>
                    <select id="bahan_baku_id" name="bahan_baku_id"
                            class="form-select @error('bahan_baku_id') is-invalid @enderror" autofocus>
                        <option value="">-- Pilih bahan baku --</option>
                        @foreach ($daftarBahan as $bahan)
                            {{-- Informasi stok dan harga disimpan di atribut data-*,
                                 lalu ditampilkan JavaScript saat bahan dipilih --}}
                            <option value="{{ $bahan->id }}"
                                    data-satuan="{{ $bahan->satuan }}"
                                    data-stok="{{ Number::format($bahan->stok, maxPrecision: 3, locale: 'id') }} {{ $bahan->satuan }}"
                                    data-harga="{{ Number::currency($bahan->harga_rata_rata, in: 'IDR', locale: 'id') }}"
                                    @selected(old('bahan_baku_id') == $bahan->id)>
                                {{ $bahan->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('bahan_baku_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Informasi kondisi bahan terpilih saat ini, disembunyikan sampai bahan dipilih --}}
                <div id="info-bahan" class="alert alert-secondary py-2 d-none">
                    Stok saat ini: <strong id="info-stok"></strong><br>
                    Harga rata-rata saat ini: <strong id="info-harga"></strong>
                </div>

                <div class="mb-3">
                    <label for="tanggal" class="form-label">Tanggal</label>
                    {{-- max = hari ini, supaya kalender tidak menawarkan tanggal masa depan --}}
                    <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}"
                           max="{{ now()->toDateString() }}"
                           class="form-control @error('tanggal') is-invalid @enderror">
                    @error('tanggal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="jumlah" class="form-label">Jumlah</label>
                    <div class="input-group has-validation">
                        <input type="number" id="jumlah" name="jumlah" value="{{ old('jumlah') }}" step="0.001" min="0"
                               class="form-control @error('jumlah') is-invalid @enderror">
                        <span class="input-group-text satuan-terpilih">satuan</span>
                        @error('jumlah')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="harga_beli" class="form-label">Harga Beli per Satuan (Rp)</label>
                    <div class="input-group has-validation">
                        <input type="number" id="harga_beli" name="harga_beli" value="{{ old('harga_beli') }}" step="0.01" min="0"
                               class="form-control @error('harga_beli') is-invalid @enderror">
                        <span class="input-group-text">per <span class="satuan-terpilih ms-1">satuan</span></span>
                        @error('harga_beli')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="/barang-masuk" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>

    <script>
        const pilihBahan = document.getElementById('bahan_baku_id');

        // Tampilkan stok, harga rata-rata, dan satuan dari bahan yang dipilih.
        function tampilkanInfoBahan() {
            const pilihan = pilihBahan.selectedOptions[0];
            const adaPilihan = pilihan.value !== '';

            document.getElementById('info-bahan').classList.toggle('d-none', ! adaPilihan);
            document.getElementById('info-stok').textContent = pilihan.dataset.stok || '';
            document.getElementById('info-harga').textContent = pilihan.dataset.harga || '';
            document.querySelectorAll('.satuan-terpilih').forEach(function (el) {
                el.textContent = pilihan.dataset.satuan || 'satuan';
            });
        }

        pilihBahan.addEventListener('change', tampilkanInfoBahan);

        // Jalankan sekali saat halaman dibuka, untuk kasus validasi gagal dan bahan sudah terpilih.
        tampilkanInfoBahan();
    </script>
@endsection
