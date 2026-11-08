@extends('layouts.app')

@section('judul', 'Kasir')

@section('konten')
    @php
        // Format rupiah gaya Indonesia (hanya tampilan).
        $rupiah = fn ($nilai) => Number::currency($nilai, in: 'IDR', locale: 'id');
    @endphp

    {{-- Gaya khusus halaman kasir --}}
    <style>
        /* Dua kolom: menu sekitar 65%, pesanan sekitar 35%. Di layar sempit menjadi satu kolom. */
        .tata-kasir { display: grid; grid-template-columns: minmax(0, 65fr) minmax(300px, 35fr); gap: 20px; align-items: start; }
        @media (max-width: 991.98px) { .tata-kasir { grid-template-columns: minmax(0, 1fr); } }

        /* Panel pesanan menempel di atas saat halaman digulir */
        .kolom-pesanan { position: sticky; top: 24px; }

        /* Grid kartu menu: 3 kartu per baris (2 di layar kecil) */
        .grid-menu { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        @media (max-width: 767.98px) { .grid-menu { grid-template-columns: repeat(2, minmax(0, 1fr)); } }

        /* Kartu menu berupa tombol: mengklik di mana saja pada kartu sama dengan menekan Tambah */
        .kartu-menu {
            display: flex; flex-direction: column; align-items: flex-start; gap: 4px; min-height: 118px;
            padding: 16px; text-align: left; color: var(--teks); background: #fff;
            border: 1px solid var(--card-border); border-radius: 12px;
            transition: border-color .15s, background-color .15s;
        }
        .kartu-menu:hover:not(:disabled) { border-color: var(--primary); background: #FAFCFE; }
        .kartu-menu:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
        .kartu-menu-nama { font-weight: 600; font-size: 15px; line-height: 1.3; }
        .kartu-menu-harga { color: var(--teks-muted); }
        .kartu-menu-tombol {
            margin-top: auto; padding: 4px 14px; border: 1px solid var(--primary); border-radius: 6px;
            color: var(--primary); font-size: 13px; font-weight: 500;
        }
        .kartu-menu:hover:not(:disabled) .kartu-menu-tombol { background: var(--primary); color: #fff; }
        /* Menu yang stok bahannya tidak cukup: redup dan tidak bisa diklik */
        .kartu-menu:disabled { opacity: .55; cursor: not-allowed; }
        .label-stok-kurang {
            margin-top: auto; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 600;
            color: var(--danger); background: var(--danger-tipis);
        }

        /* Daftar pesanan: digulir sendiri kalau item banyak, supaya panel tetap muat di layar */
        .daftar-pesanan { max-height: 45vh; overflow-y: auto; }
        .baris-pesanan { padding: 12px 20px; border-bottom: 1px solid var(--card-border); }
        .baris-pesanan-atas, .baris-pesanan-bawah { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
        .baris-pesanan-bawah { margin-top: 6px; }
        .baris-pesanan-nama { font-weight: 600; }
        .pengatur-jumlah { display: inline-flex; align-items: center; gap: 8px; }
        .pengatur-jumlah .btn { width: 32px; padding: 1px 0; font-weight: 600; }
        .angka-jumlah { min-width: 26px; text-align: center; font-weight: 600; }
        .tombol-hapus-item { padding: 0; font-size: 13px; color: var(--danger); text-decoration: none; }
        .tombol-hapus-item:hover { color: var(--danger-gelap); text-decoration: underline; }
        .pesanan-kosong { padding: 32px 20px; text-align: center; color: var(--teks-muted); }
    </style>

    <x-kepala-halaman judul="Kasir" keterangan="Pilih menu untuk mencatat pesanan pembeli" />

    {{-- Hari ini sudah ditutup buku: kasir diberi tahu sebelum mengisi keranjang --}}
    @if ($sudahTutupBuku)
        <div class="alert alert-warning">
            <strong>Hari ini sudah ditutup buku.</strong>
            Transaksi baru tidak dapat dicatat sampai besok. Tombol Simpan Transaksi dinonaktifkan.
        </div>
    @endif

    {{-- Pesan berhasil: total transaksi dan tautan ke detailnya --}}
    @if (session('transaksiTersimpan'))
        @php $tersimpan = session('transaksiTersimpan'); @endphp
        <div class="alert alert-success alert-dismissible fade show">
            <strong>Transaksi berhasil disimpan.</strong>
            Total {{ $rupiah($tersimpan['total']) }}.
            <a href="/transaksi/{{ $tersimpan['id'] }}" class="alert-link ms-1">Lihat detail</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    {{-- Stok tidak cukup (dari TransaksiService). Keranjang tetap terisi. --}}
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

    {{-- Kesalahan validasi lain dari TransaksiRequest (misalnya hari ini sudah ditutup buku) --}}
    @php $kesalahanLain = collect($errors->getMessages())->except('stok')->flatten()->unique(); @endphp
    @if ($kesalahanLain->isNotEmpty())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($kesalahanLain as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="tata-kasir">
        {{-- ============ Kolom kiri: pencarian dan grid menu ============ --}}
        <section aria-label="Daftar menu">
            <input type="search" id="cari-menu" class="form-control form-control-lg jarak-bagian"
                   placeholder="Cari menu..." aria-label="Cari menu" autocomplete="off" autofocus>

            <div class="grid-menu" id="grid-menu">
                @foreach ($daftarMenu as $menu)
                    <button type="button" class="kartu-menu" data-id="{{ $menu['id'] }}"
                            data-nama="{{ mb_strtolower($menu['nama']) }}" @disabled(! $menu['cukup'])
                            aria-label="{{ $menu['cukup'] ? 'Tambah ' . $menu['nama'] : $menu['nama'] . ', stok tidak cukup' }}">
                        <span class="kartu-menu-nama">{{ $menu['nama'] }}</span>
                        <span class="kartu-menu-harga">{{ $rupiah($menu['harga']) }}</span>
                        @if ($menu['cukup'])
                            <span class="kartu-menu-tombol">Tambah</span>
                        @else
                            <span class="label-stok-kurang">Stok tidak cukup</span>
                        @endif
                    </button>
                @endforeach
            </div>

            <div id="menu-tidak-ada" class="pesanan-kosong d-none">Menu tidak ditemukan.</div>
            @if ($daftarMenu->isEmpty())
                <div class="pesanan-kosong">Belum ada menu. Tambahkan menu di halaman Menu.</div>
            @endif
        </section>

        {{-- ============ Kolom kanan: pesanan (menempel saat digulir) ============ --}}
        <aside class="kolom-pesanan" aria-label="Pesanan">
            <div class="card">
                <div class="card-header fw-semibold">Pesanan</div>

                {{-- Diisi JavaScript dari isi keranjang --}}
                <div class="daftar-pesanan" id="daftar-pesanan"></div>

                <div class="card-body border-top">
                    <div class="d-flex justify-content-between" style="color: var(--teks-muted);">
                        <span>Jumlah porsi</span>
                        <span id="jumlah-porsi">0 porsi</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-baseline mt-2">
                        <span class="label-kartu">Total</span>
                        <span class="angka-besar" id="total-pesanan">{{ $rupiah(0) }}</span>
                    </div>

                    <form method="POST" action="/kasir" id="form-kasir" class="mt-3">
                        @csrf
                        {{-- Tanggal tidak bisa diubah: transaksi selalu dicatat pada hari ini (ditentukan server) --}}
                        <div class="d-flex justify-content-between align-items-baseline">
                            <span class="small" style="color: var(--teks-muted);">Tanggal</span>
                            <span class="fw-semibold">{{ today()->locale('id')->translatedFormat('l, j F Y') }}</span>
                        </div>

                        {{-- Isian tersembunyi item[i][menu_id] dan item[i][jumlah], dibuat JavaScript.
                             Bentuknya sama dengan formulir lama, jadi TransaksiRequest tidak berubah. --}}
                        <div id="isian-item"></div>

                        <div class="d-flex align-items-center gap-2 mt-3">
                            <button type="submit" id="tombol-simpan" class="btn btn-primary flex-grow-1" disabled>Simpan Transaksi</button>
                            <button type="button" id="tombol-kosongkan" class="btn btn-sm btn-outline-danger" disabled>Kosongkan</button>
                        </div>
                    </form>
                </div>
            </div>
        </aside>
    </div>

    <script>
        (function () {
            // Data menu dari server: id, nama, harga jual, dan apakah stok cukup untuk satu porsi.
            const daftarMenu = @json($daftarMenu);
            const petaMenu = new Map(daftarMenu.map((menu) => [menu.id, menu]));

            // true jika hari ini sudah ditutup buku (dari server)
            const sudahTutupBuku = @json($sudahTutupBuku);

            // Isi keranjang: [{ id: nomor menu, jumlah: porsi }]
            let keranjang = [];

            const ambil = (id) => document.getElementById(id);
            // Dua angka di belakang koma ditulis tegas, supaya sama dengan format rupiah dari server
            // (sebagian peramban menampilkan rupiah tanpa desimal jika tidak diminta).
            const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', {
                style: 'currency', currency: 'IDR', minimumFractionDigits: 2, maximumFractionDigits: 2,
            }).format(angka);

            // Membuat elemen dengan kelas dan teks. Teks diisi lewat textContent, bukan HTML,
            // supaya nama menu yang mengandung tanda khusus tetap aman ditampilkan.
            function buat(tag, kelas, teks) {
                const elemen = document.createElement(tag);
                if (kelas) elemen.className = kelas;
                if (teks !== undefined) elemen.textContent = teks;
                return elemen;
            }

            // Menambah menu ke keranjang. Jika sudah ada, jumlahnya dinaikkan (bukan baris baru).
            function tambahMenu(id, jumlah = 1) {
                const sudahAda = keranjang.find((item) => item.id === id);
                if (sudahAda) {
                    sudahAda.jumlah += jumlah;
                } else {
                    keranjang.push({ id: id, jumlah: jumlah });
                }
                tampilkan();
            }

            // Menaikkan atau menurunkan jumlah. Jika menjadi 0, baris dihapus.
            function ubahJumlah(id, selisih) {
                const item = keranjang.find((item) => item.id === id);
                if (!item) return;
                item.jumlah += selisih;
                if (item.jumlah <= 0) {
                    keranjang = keranjang.filter((item) => item.id !== id);
                }
                tampilkan();
            }

            function hapusItem(id) {
                keranjang = keranjang.filter((item) => item.id !== id);
                tampilkan();
            }

            // Menggambar ulang daftar pesanan, total, dan isian tersembunyi dari isi keranjang.
            function tampilkan() {
                const wadah = ambil('daftar-pesanan');
                wadah.replaceChildren();

                if (keranjang.length === 0) {
                    wadah.append(buat('div', 'pesanan-kosong', 'Belum ada item. Pilih menu di sebelah kiri.'));
                }

                keranjang.forEach((item) => {
                    const menu = petaMenu.get(item.id);
                    const baris = buat('div', 'baris-pesanan');

                    // Baris atas: nama menu dan tombol hapus baris
                    const atas = buat('div', 'baris-pesanan-atas');
                    const tombolHapus = buat('button', 'btn btn-link tombol-hapus-item', 'Hapus');
                    tombolHapus.type = 'button';
                    tombolHapus.dataset.aksi = 'hapus';
                    tombolHapus.dataset.id = item.id;
                    tombolHapus.setAttribute('aria-label', 'Hapus ' + menu.nama);
                    atas.append(buat('span', 'baris-pesanan-nama', menu.nama), tombolHapus);

                    // Baris bawah: tombol kurang, jumlah, tombol tambah, lalu subtotal
                    const bawah = buat('div', 'baris-pesanan-bawah');
                    const pengatur = buat('div', 'pengatur-jumlah');
                    const tombolKurang = buat('button', 'btn btn-sm btn-saring', '−');
                    tombolKurang.type = 'button';
                    tombolKurang.dataset.aksi = 'kurang';
                    tombolKurang.dataset.id = item.id;
                    tombolKurang.setAttribute('aria-label', 'Kurangi ' + menu.nama);
                    const tombolTambah = buat('button', 'btn btn-sm btn-saring', '+');
                    tombolTambah.type = 'button';
                    tombolTambah.dataset.aksi = 'tambah';
                    tombolTambah.dataset.id = item.id;
                    tombolTambah.setAttribute('aria-label', 'Tambah ' + menu.nama);
                    pengatur.append(tombolKurang, buat('span', 'angka-jumlah', String(item.jumlah)), tombolTambah);
                    bawah.append(pengatur, buat('span', 'fw-semibold', formatRupiah(menu.harga * item.jumlah)));

                    baris.append(atas, bawah);
                    wadah.append(baris);
                });

                // Total porsi dan total harga (hanya tampilan; server menghitung ulang dari harga di basis data)
                const totalPorsi = keranjang.reduce((jumlah, item) => jumlah + item.jumlah, 0);
                const totalHarga = keranjang.reduce((jumlah, item) => jumlah + petaMenu.get(item.id).harga * item.jumlah, 0);
                ambil('jumlah-porsi').textContent = totalPorsi + ' porsi';
                ambil('total-pesanan').textContent = formatRupiah(totalHarga);

                // Isian tersembunyi yang dikirim ke server
                const isian = ambil('isian-item');
                isian.replaceChildren();
                keranjang.forEach((item, urutan) => {
                    [['menu_id', item.id], ['jumlah', item.jumlah]].forEach(([kolom, nilai]) => {
                        const input = buat('input');
                        input.type = 'hidden';
                        input.name = `item[${urutan}][${kolom}]`;
                        input.value = nilai;
                        isian.append(input);
                    });
                });

                // Tombol Simpan dan Kosongkan nonaktif selama keranjang kosong
                // Simpan juga selalu nonaktif jika hari ini sudah ditutup buku.
                ambil('tombol-simpan').disabled = keranjang.length === 0 || sudahTutupBuku;
                ambil('tombol-kosongkan').disabled = keranjang.length === 0;
            }

            // Klik kartu menu = tambah ke keranjang. Kartu yang stoknya tidak cukup nonaktif (disabled).
            ambil('grid-menu').addEventListener('click', (event) => {
                const kartu = event.target.closest('.kartu-menu');
                if (kartu && !kartu.disabled) {
                    tambahMenu(Number(kartu.dataset.id));
                }
            });

            // Tombol kurang, tambah, dan hapus di daftar pesanan
            ambil('daftar-pesanan').addEventListener('click', (event) => {
                const tombol = event.target.closest('button[data-aksi]');
                if (!tombol) return;
                const id = Number(tombol.dataset.id);
                if (tombol.dataset.aksi === 'tambah') ubahJumlah(id, 1);
                if (tombol.dataset.aksi === 'kurang') ubahJumlah(id, -1);
                if (tombol.dataset.aksi === 'hapus') hapusItem(id);
            });

            // Kosongkan keranjang, dengan konfirmasi supaya tidak terhapus tidak sengaja
            ambil('tombol-kosongkan').addEventListener('click', () => {
                if (keranjang.length > 0 && confirm('Kosongkan semua item pesanan?')) {
                    keranjang = [];
                    tampilkan();
                    ambil('cari-menu').focus();
                }
            });

            // Pencarian menu: sembunyikan kartu yang namanya tidak cocok
            ambil('cari-menu').addEventListener('input', (event) => {
                const kata = event.target.value.trim().toLowerCase();
                let jumlahTampil = 0;
                document.querySelectorAll('.kartu-menu').forEach((kartu) => {
                    const cocok = kartu.dataset.nama.includes(kata);
                    kartu.classList.toggle('d-none', !cocok);
                    if (cocok) jumlahTampil++;
                });
                ambil('menu-tidak-ada').classList.toggle('d-none', jumlahTampil > 0 || daftarMenu.length === 0);
            });

            // Cegah transaksi tersimpan dua kali karena tombol ditekan berulang
            ambil('form-kasir').addEventListener('submit', () => {
                const tombol = ambil('tombol-simpan');
                tombol.disabled = true;
                tombol.textContent = 'Menyimpan...';
            });

            // Jika penyimpanan sebelumnya gagal (misalnya stok tidak cukup), isi keranjang dikembalikan.
            const keranjangAwal = @json($keranjangAwal);
            keranjangAwal.forEach((item) => {
                const id = Number(item.menu_id);
                const jumlah = parseInt(item.jumlah, 10);
                if (petaMenu.has(id) && jumlah > 0) {
                    const sudahAda = keranjang.find((b) => b.id === id);
                    sudahAda ? (sudahAda.jumlah += jumlah) : keranjang.push({ id: id, jumlah: jumlah });
                }
            });

            tampilkan();
        })();
    </script>
@endsection
