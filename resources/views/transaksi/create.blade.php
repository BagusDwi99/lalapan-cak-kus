@extends('layouts.app')

@section('judul', 'Transaksi Baru')

@section('konten')
    @php
        // Nomor untuk baris baru dari JavaScript, dimulai setelah nomor terbesar.
        $indeksBerikutnya = empty($daftarItem) ? 0 : max(array_map('intval', array_keys($daftarItem))) + 1;
    @endphp

    <h1 class="h3 mb-3">Transaksi Baru</h1>

    {{-- Pesan stok tidak cukup dari TransaksiService --}}
    @if ($errors->has('stok'))
        <div class="alert alert-danger">
            <strong>Transaksi dibatalkan karena stok bahan baku tidak cukup:</strong>
            <ul class="mb-0">
                @foreach ($errors->get('stok') as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @error('item')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    <div class="card" style="max-width: 900px;">
        <div class="card-body">
            <form method="POST" action="/transaksi">
                @csrf

                <div class="mb-3" style="max-width: 220px;">
                    <label for="tanggal" class="form-label">Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}"
                           max="{{ now()->toDateString() }}"
                           class="form-control @error('tanggal') is-invalid @enderror">
                    @error('tanggal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <table class="table align-middle mb-2">
                    <thead class="table-light">
                        <tr>
                            <th>Menu</th>
                            <th style="width: 130px;">Jumlah Porsi</th>
                            <th class="text-end" style="width: 150px;">Harga Jual</th>
                            <th class="text-end" style="width: 160px;">Subtotal</th>
                            <th style="width: 80px;"></th>
                        </tr>
                    </thead>
                    <tbody id="daftar-item">
                        @foreach ($daftarItem as $i => $baris)
                            @include('transaksi._baris_item', ['i' => $i, 'baris' => $baris])
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-semibold">
                            <td colspan="3" class="text-end">Total</td>
                            <td class="text-end fs-5" id="total-keseluruhan">Rp 0,00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                <button type="button" id="tombol-tambah-item" class="btn btn-outline-primary btn-sm mb-4">+ Tambah Item</button>

                <div>
                    <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                    <a href="/transaksi" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Cetakan baris kosong untuk JavaScript. __INDEX__ diganti nomor baris. --}}
    <template id="template-baris">
        @include('transaksi._baris_item', ['i' => '__INDEX__', 'baris' => ['jumlah' => 1]])
    </template>

    <script>
        const daftarItem = document.getElementById('daftar-item');
        const templateBaris = document.getElementById('template-baris');
        let indeksBerikutnya = {{ $indeksBerikutnya }};

        // Format angka menjadi rupiah, contoh 20000 -> "Rp 20.000,00".
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(angka);
        }

        // Hitung ulang harga jual, subtotal tiap baris, dan total keseluruhan.
        // Ini hanya tampilan; server tetap menghitung ulang dari harga menu di basis data.
        function hitungUlang() {
            let total = 0;

            daftarItem.querySelectorAll('.baris-item').forEach(function (baris) {
                const pilihan = baris.querySelector('.pilih-menu').selectedOptions[0];
                const harga = parseFloat(pilihan.dataset.harga || 0);
                const jumlah = parseInt(baris.querySelector('.jumlah-porsi').value) || 0;
                const subtotal = harga * jumlah;

                baris.querySelector('.harga-jual').textContent = pilihan.value ? formatRupiah(harga) : '-';
                baris.querySelector('.subtotal').textContent = pilihan.value ? formatRupiah(subtotal) : '-';
                total += subtotal;
            });

            document.getElementById('total-keseluruhan').textContent = formatRupiah(total);
        }

        // Tombol Hapus dinonaktifkan jika tinggal satu baris (minimal satu item).
        function perbaruiTombolHapus() {
            const semuaBaris = daftarItem.querySelectorAll('.baris-item');
            semuaBaris.forEach(function (baris) {
                baris.querySelector('.tombol-hapus-baris').disabled = semuaBaris.length === 1;
            });
        }

        // Tambah baris baru dari cetakan.
        document.getElementById('tombol-tambah-item').addEventListener('click', function () {
            const html = templateBaris.innerHTML.replaceAll('__INDEX__', indeksBerikutnya);
            indeksBerikutnya++;
            daftarItem.insertAdjacentHTML('beforeend', html);
            perbaruiTombolHapus();
            hitungUlang();
        });

        // Hapus baris tempat tombol Hapus diklik.
        daftarItem.addEventListener('click', function (event) {
            if (event.target.classList.contains('tombol-hapus-baris')) {
                event.target.closest('tr').remove();
                perbaruiTombolHapus();
                hitungUlang();
            }
        });

        // Setiap menu diganti atau jumlah porsi diketik, hitung ulang.
        daftarItem.addEventListener('change', hitungUlang);
        daftarItem.addEventListener('input', hitungUlang);

        perbaruiTombolHapus();
        hitungUlang();
    </script>
@endsection
