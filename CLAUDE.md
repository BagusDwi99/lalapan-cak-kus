# Sistem Informasi Pencatatan Stok dan Laba — Warung Lalapan Cak Kus

Skripsi S1 Sistem Informasi, ITB STIKOM Bali.
Objek penelitian: Warung Lalapan Cak Kus, Ketewel, Sukawati, Gianyar.

## Teknologi
- PHP 8.4, Laravel, MySQL. Basis data bernama lalapan_cak_kus.
- Arsitektur MVC. Tampilan memakai Blade.
- TANPA Vue, React, Livewire, atau Inertia.
- Nama tabel, kolom, rute, dan teks antarmuka dalam bahasa Indonesia.
- Komentar kode dalam bahasa Indonesia.
- Tampilan memakai Bootstrap 5 lewat CDN. TANPA npm, Vite, atau Tailwind.
- Autentikasi ditulis manual memakai Auth facade, bukan Breeze atau Jetstream.

## Aturan yang tidak boleh dilanggar
- Perhitungan Moving Average dan MAPE WAJIB berada di
  app/Services/MovingAverageService.php dan dipanggil dari controller.
  Jangan menaruh rumus di controller, model, atau Blade.
- Jangan menambah tabel di luar sepuluh tabel di bawah.
- Jangan menambah fitur yang tidak diminta.
- Buat kode sesederhana mungkin. Ini skripsi S1, bukan aplikasi produksi.
- Jelaskan setiap kode yang dibuat dengan bahasa yang mudah dipahami.

## Sepuluh tabel
1. users — owner dan admin/kasir, ada kolom peran
2. bahan_baku — nama, satuan, stok, stok_minimum, harga_rata_rata
3. menu — nama, harga_jual
4. menu_bahan_baku — pivot menu dan bahan baku, jumlah pemakaian per porsi
5. barang_masuk — bahan_baku_id, tanggal, jumlah, harga_beli
6. transaksi — tanggal, total, user_id
7. detail_transaksi — transaksi_id, menu_id, jumlah, harga_jual, hpp, laba_kotor
8. biaya_operasional — nama, jumlah, periode
9. prediksi — bahan_baku_id, tanggal, periode_n, metode (SMA/WMA),
   nilai_prediksi, nilai_aktual, ape
10. tutup_buku — tanggal, total_penjualan, total_laba

## Metode Moving Average punya dua peran
1. Peramalan kebutuhan bahan baku.
   Dua varian dihitung bersamaan dari data masukan yang sama:
   - SMA: rata-rata n periode terakhir, semua periode berbobot sama
   - WMA: bobot 0,5 / 0,3 / 0,2, terbesar pada data paling baru
   Akurasi diukur dengan MAPE. Varian dengan MAPE terkecil direkomendasikan,
   dan penetapannya dilakukan per bahan baku.
2. Moving average cost method untuk harga pokok penjualan.
   Harga rata-rata dihitung ulang SAAT PENCATATAN BARANG MASUK bila harga
   belinya berbeda, bukan saat penjualan terjadi.

## Alur laba
laba kotor = harga jual - HPP, dihitung per item pada detail_transaksi
laba bersih = laba kotor - biaya operasional, dihitung per periode bulanan

## Sumber data prediksi
Pemakaian bahan baku harian TIDAK diinput manual. Diturunkan dari
transaksi -> detail_transaksi -> menu_bahan_baku.