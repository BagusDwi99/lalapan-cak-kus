# Sistem Informasi Pencatatan Stok dan Laba — Warung Lalapan Cak Kus

Skripsi S1 Sistem Informasi, ITB STIKOM Bali.
Objek penelitian: Warung Lalapan Cak Kus, Ketewel, Sukawati, Gianyar.

## Teknologi
- PHP 8.4, Laravel, MySQL. Basis data bernama lalapan_cak_kus.
- Arsitektur MVC. Tampilan memakai Blade.
- TANPA Vue, React, Livewire, atau Inertia.
- Tampilan memakai Bootstrap 5 dari file lokal di public/vendor/bootstrap,
  BUKAN dari CDN. TANPA npm, Vite, atau Tailwind.
- Grafik memakai Chart.js lokal di public/vendor/chartjs.
- Autentikasi ditulis manual memakai Auth facade, bukan Breeze atau Jetstream.
- Nama tabel, kolom, rute, dan teks antarmuka dalam bahasa Indonesia.
- Komentar kode dalam bahasa Indonesia.

## Aturan yang tidak boleh dilanggar
- Peramalan (SMA, WMA, MAPE) WAJIB berada di
  app/Services/MovingAverageService.php. Moving average cost berada di
  BahanBaku::hitungHargaRataRataBaru(), dan HPP per porsi di
  Menu::hitungHppPerPorsi(). Jangan menaruh rumus di controller atau Blade.
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

## Kriteria interpretasi MAPE (kriteria Lewis)
Pakai istilah ini persis, jangan diganti sinonim:
- MAPE < 10%          -> Sangat akurat
- 10% <= MAPE < 20%   -> Akurat
- 20% <= MAPE < 50%   -> Wajar
- MAPE >= 50%         -> Tidak akurat

## Parameter peramalan (sudah final)
- Jumlah periode n = 3 untuk KEDUA varian, SMA maupun WMA.
  Tidak ada pilihan periode lain di antarmuka.
- Bobot WMA tetap: 0,5 untuk satu periode sebelumnya,
  0,3 untuk dua periode sebelumnya, 0,2 untuk tiga periode sebelumnya.
- Alasan n dikunci sama: perbandingan SMA dan WMA hanya sah kalau
  jumlah periodenya identik. Yang boleh berbeda hanya cara pembobotan.

## Pencatatan yang bersifat final
- barang_masuk dan transaksi TIDAK BISA diubah atau dihapus, seperti jurnal.
  Rute ubah dan hapus memang sengaja tidak dibuat.
- Transaksi dan barang masuk ditolak kalau tanggalnya sudah ada di tutup_buku.
- biaya_operasional BOLEH diubah dan dihapus, karena sifatnya beban bulanan.

## Hak akses
- Kasir TIDAK BOLEH melihat HPP, laba kotor, dan laba bersih di mana pun,
  termasuk di halaman Menu, Transaksi, dan Tutup Buku.
  Penyaringan dilakukan di sisi server lewat User::bolehLihatLaba(),
  bukan disembunyikan dengan CSS.
- Halaman khusus owner: Biaya Operasional, Laba Rugi, Prediksi, Laporan.
  Dilindungi middleware peran:owner, bukan sekadar menu disembunyikan.

## Konsistensi data
- Setiap operasi yang mengubah stok dibungkus transaksi basis data
  dan memakai lockForUpdate() saat membaca bahan_baku.
- Pembulatan dilakukan di akhir saja, saat menyimpan atau menampilkan.
  Jangan membulatkan nilai antara karena kesalahannya menumpuk.