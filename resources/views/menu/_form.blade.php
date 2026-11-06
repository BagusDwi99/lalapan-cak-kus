{{--
    Isian formulir yang dipakai bersama oleh halaman tambah dan ubah menu.
    Variabel: $menu, $komposisi (baris-baris komposisi), $daftarBahan (pilihan bahan baku).
--}}
@php
    // Daftar satuan per bahan, contoh: [1 => 'potong', 6 => 'kg'].
    $satuanBahan = $daftarBahan->pluck('satuan', 'id');

    // Nomor untuk baris baru yang ditambahkan lewat JavaScript, dimulai setelah nomor terbesar.
    $indeksBerikutnya = empty($komposisi) ? 0 : max(array_map('intval', array_keys($komposisi))) + 1;
@endphp

<div class="mb-3">
    <label for="nama" class="form-label">Nama Menu</label>
    <input type="text" id="nama" name="nama" value="{{ old('nama', $menu->nama) }}"
           class="form-control @error('nama') is-invalid @enderror" autofocus>
    @error('nama')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-4">
    <label for="harga_jual" class="form-label">Harga Jual (Rp)</label>
    <input type="number" id="harga_jual" name="harga_jual" step="1" min="0"
           value="{{ old('harga_jual', $menu->exists ? $menu->harga_jual + 0 : '') }}"
           class="form-control @error('harga_jual') is-invalid @enderror">
    @error('harga_jual')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<h2 class="h5">Komposisi Bahan Baku per Porsi</h2>

@error('komposisi')
    <div class="alert alert-danger py-2">{{ $message }}</div>
@enderror

<table class="table align-middle mb-2">
    <thead class="table-light">
        <tr>
            <th>Bahan Baku</th>
            <th style="width: 170px;">Jumlah per Porsi</th>
            <th style="width: 90px;">Satuan</th>
            <th style="width: 80px;"></th>
        </tr>
    </thead>
    <tbody id="daftar-komposisi">
        @foreach ($komposisi as $i => $baris)
            @include('menu._baris_komposisi', ['i' => $i, 'baris' => $baris])
        @endforeach
    </tbody>
</table>

<button type="button" id="tombol-tambah-bahan" class="btn btn-outline-primary btn-sm mb-4">+ Tambah Bahan</button>

{{-- Cetakan baris kosong. Tidak tampil di layar; JavaScript menyalinnya
     setiap kali tombol "Tambah Bahan" ditekan. __INDEX__ diganti nomor baris. --}}
<template id="template-baris">
    @include('menu._baris_komposisi', ['i' => '__INDEX__', 'baris' => []])
</template>

<script>
    const daftarKomposisi = document.getElementById('daftar-komposisi');
    const templateBaris = document.getElementById('template-baris');
    let indeksBerikutnya = {{ $indeksBerikutnya }};

    // Tombol Hapus dinonaktifkan jika tinggal satu baris (minimal satu bahan).
    function perbaruiTombolHapus() {
        const semuaBaris = daftarKomposisi.querySelectorAll('.baris-komposisi');
        semuaBaris.forEach(function (baris) {
            baris.querySelector('.tombol-hapus-baris').disabled = semuaBaris.length === 1;
        });
    }

    // Tambah baris: salin cetakan, ganti __INDEX__ dengan nomor baru, tempel di akhir tabel.
    document.getElementById('tombol-tambah-bahan').addEventListener('click', function () {
        const html = templateBaris.innerHTML.replaceAll('__INDEX__', indeksBerikutnya);
        indeksBerikutnya++;
        daftarKomposisi.insertAdjacentHTML('beforeend', html);
        perbaruiTombolHapus();
    });

    // Hapus baris: buang baris tempat tombol Hapus diklik.
    daftarKomposisi.addEventListener('click', function (event) {
        if (event.target.classList.contains('tombol-hapus-baris')) {
            event.target.closest('tr').remove();
            perbaruiTombolHapus();
        }
    });

    // Ganti bahan baku: tampilkan satuan dari atribut data-satuan pilihan yang dipilih.
    daftarKomposisi.addEventListener('change', function (event) {
        if (event.target.classList.contains('pilih-bahan')) {
            const pilihan = event.target.selectedOptions[0];
            const kolomSatuan = event.target.closest('tr').querySelector('.satuan-bahan');
            kolomSatuan.textContent = pilihan.dataset.satuan || '-';
        }
    });

    perbaruiTombolHapus();
</script>
