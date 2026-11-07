{{--
    Gaya tampilan aplikasi: huruf Inter lokal, palet warna, dan penyesuaian Bootstrap.
    Dipakai bersama oleh layouts/app.blade.php dan halaman login, supaya tampilannya seragam.
    Harus dimuat SETELAH bootstrap.min.css supaya bisa menimpa warnanya.
--}}
    <style>
        /* ---------- Huruf Inter, disimpan lokal di public/vendor/fonts ---------- */
        @font-face { font-family: 'Inter'; font-style: normal; font-weight: 400; font-display: swap;
            src: url('{{ asset('vendor/fonts/inter-latin-400-normal.woff2') }}') format('woff2'); }
        @font-face { font-family: 'Inter'; font-style: normal; font-weight: 500; font-display: swap;
            src: url('{{ asset('vendor/fonts/inter-latin-500-normal.woff2') }}') format('woff2'); }
        @font-face { font-family: 'Inter'; font-style: normal; font-weight: 600; font-display: swap;
            src: url('{{ asset('vendor/fonts/inter-latin-600-normal.woff2') }}') format('woff2'); }
        @font-face { font-family: 'Inter'; font-style: normal; font-weight: 700; font-display: swap;
            src: url('{{ asset('vendor/fonts/inter-latin-700-normal.woff2') }}') format('woff2'); }

        /* ---------- Palet warna aplikasi ---------- */
        :root {
            --sidebar-bg: #152235;
            --sidebar-text: #C6D1E2;
            --sidebar-active: #1F4E79;
            --page-bg: #F1F3F6;
            --card-border: #DDE3EC;
            --teks: #1B2230;
            --teks-muted: #55637A;
            --primary: #1F4E79;
            --danger: #BE123C;

            /* Turunan untuk keadaan hover/aktif dan latar tipis */
            --primary-gelap: #173B5C;
            --primary-tipis: #E4ECF4;
            --danger-gelap: #9F0F32;
            --danger-tipis: #FBE8ED;

            /* Variabel Bootstrap diarahkan ke palet di atas, sehingga berlaku di seluruh halaman */
            --bs-body-font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            --bs-body-font-size: 14px;
            /* Catatan: --bs-body-bg sengaja tidak diubah, karena Bootstrap juga memakainya untuk
               latar kartu, tabel, dan isian. Latar halaman diatur langsung pada body di bawah. */
            --bs-body-color: var(--teks);
            --bs-secondary-color: var(--teks-muted);
            --bs-border-color: var(--card-border);
            --bs-primary: var(--primary);
            --bs-primary-rgb: 31, 78, 121;
            --bs-primary-text-emphasis: var(--primary-gelap);
            --bs-primary-bg-subtle: var(--primary-tipis);
            --bs-primary-border-subtle: #B9CBDD;
            --bs-danger: var(--danger);
            --bs-danger-rgb: 190, 18, 60;
            --bs-danger-text-emphasis: var(--danger-gelap);
            --bs-danger-bg-subtle: var(--danger-tipis);
            --bs-danger-border-subtle: #F2B8C6;
            --bs-link-color: var(--primary);
            --bs-link-color-rgb: 31, 78, 121;
            --bs-link-hover-color: var(--primary-gelap);
            --bs-link-hover-color-rgb: 23, 59, 92;
            --bs-focus-ring-color: rgba(31, 78, 121, .25);
            --bs-form-invalid-color: var(--danger);
            --bs-form-invalid-border-color: var(--danger);
        }

        body {
            background: var(--page-bg);
            color: var(--teks);
            /* Digit sama lebar, supaya angka antarbaris tabel sejajar */
            font-variant-numeric: tabular-nums;
        }

        /* ---------- Komponen Bootstrap yang warnanya tertulis langsung (#0d6efd / #dc3545) ---------- */
        .btn-primary {
            --bs-btn-bg: var(--primary); --bs-btn-border-color: var(--primary);
            --bs-btn-hover-bg: var(--primary-gelap); --bs-btn-hover-border-color: var(--primary-gelap);
            --bs-btn-active-bg: var(--primary-gelap); --bs-btn-active-border-color: var(--primary-gelap);
            --bs-btn-disabled-bg: var(--primary); --bs-btn-disabled-border-color: var(--primary);
            --bs-btn-focus-shadow-rgb: 31, 78, 121;
        }
        .btn-outline-primary {
            --bs-btn-color: var(--primary); --bs-btn-border-color: var(--primary);
            --bs-btn-hover-bg: var(--primary); --bs-btn-hover-border-color: var(--primary);
            --bs-btn-active-bg: var(--primary); --bs-btn-active-border-color: var(--primary);
            --bs-btn-disabled-color: var(--primary); --bs-btn-disabled-border-color: var(--primary);
            --bs-btn-focus-shadow-rgb: 31, 78, 121;
        }
        .btn-danger {
            --bs-btn-bg: var(--danger); --bs-btn-border-color: var(--danger);
            --bs-btn-hover-bg: var(--danger-gelap); --bs-btn-hover-border-color: var(--danger-gelap);
            --bs-btn-active-bg: var(--danger-gelap); --bs-btn-active-border-color: var(--danger-gelap);
            --bs-btn-disabled-bg: var(--danger); --bs-btn-disabled-border-color: var(--danger);
            --bs-btn-focus-shadow-rgb: 190, 18, 60;
        }
        .btn-outline-danger {
            --bs-btn-color: var(--danger); --bs-btn-border-color: var(--danger);
            --bs-btn-hover-bg: var(--danger); --bs-btn-hover-border-color: var(--danger);
            --bs-btn-active-bg: var(--danger); --bs-btn-active-border-color: var(--danger);
            --bs-btn-focus-shadow-rgb: 190, 18, 60;
        }
        .pagination {
            --bs-pagination-color: var(--primary); --bs-pagination-hover-color: var(--primary-gelap);
            --bs-pagination-focus-color: var(--primary-gelap);
            --bs-pagination-active-bg: var(--primary); --bs-pagination-active-border-color: var(--primary);
            --bs-pagination-focus-box-shadow: 0 0 0 .2rem rgba(31, 78, 121, .25);
        }
        .nav-pills { --bs-nav-pills-link-active-bg: var(--primary); }
        .list-group { --bs-list-group-active-bg: var(--primary); --bs-list-group-active-border-color: var(--primary); }
        .dropdown-menu { --bs-dropdown-link-active-bg: var(--primary); }
        .progress, .progress-stacked { --bs-progress-bar-bg: var(--primary); }
        .form-control:focus, .form-select:focus, .form-check-input:focus {
            border-color: #8FA9C4;
            box-shadow: 0 0 0 .2rem rgba(31, 78, 121, .2);
        }
        .form-check-input:checked, .form-check-input[type=checkbox]:indeterminate {
            background-color: var(--primary); border-color: var(--primary);
        }
        .nav-link:focus-visible { box-shadow: 0 0 0 .2rem rgba(31, 78, 121, .25); }
        /* Ikon tanda seru pada isian tidak valid, digambar ulang dengan warna --danger */
        .form-control.is-invalid {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23BE123C'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23BE123C' stroke='none'/%3e%3c/svg%3e");
        }
        /* Selektor dibuat sama persis dengan Bootstrap supaya tidak kalah kuat */
        .form-select.is-invalid:not([multiple]):not([size]), .form-select.is-invalid:not([multiple])[size="1"] {
            --bs-form-select-bg-icon: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23BE123C'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23BE123C' stroke='none'/%3e%3c/svg%3e");
        }
        /* Latar baris tabel bertema (dipakai penanda stok menipis dan varian terpilih) */
        .table-danger { --bs-table-bg: var(--danger-tipis); --bs-table-border-color: #F2C9D3; --bs-table-color: var(--teks); }
        .table-primary { --bs-table-bg: var(--primary-tipis); --bs-table-border-color: #C9D7E5; --bs-table-color: var(--teks); }

        /* ---------- Kartu: tanpa bayangan, garis tepi tipis, sudut 12px, padding 20px ---------- */
        .card {
            --bs-card-border-color: var(--card-border);
            --bs-card-border-radius: 12px;
            --bs-card-inner-border-radius: 11px;
            --bs-card-spacer-y: 20px;
            --bs-card-spacer-x: 20px;
            --bs-card-cap-padding-y: 14px;
            --bs-card-cap-padding-x: 20px;
            --bs-card-cap-bg: #fff;
            box-shadow: none;
        }
        /* Kartu yang berisi tabel: sudut tabel ikut membulat mengikuti kartu */
        .card:has(> .table) { overflow: hidden; }
        .card-header { border-bottom-color: var(--card-border); }
        .card-footer { background: #fff; border-top-color: var(--card-border); }

        /* ---------- Huruf isi halaman ---------- */
        main h1.h3 { font-size: 24px; font-weight: 600; letter-spacing: -.01em; }
        .alert { margin-bottom: 20px; }

        /* Label kecil di kartu: 12px, huruf kapital, jarak antarhuruf */
        .label-kartu {
            font-size: 12px; font-weight: 600; text-transform: uppercase;
            letter-spacing: .06em; color: var(--teks-muted);
        }
        /* Angka besar di kartu */
        .angka-besar { font-size: 30px; font-weight: 700; line-height: 1.15; letter-spacing: -.01em; }
        /* Tabel di dalam kartu: tepi kiri-kanan sejajar dengan padding kartu (20px) */
        .tabel-kartu { margin-bottom: 0; }
        .tabel-kartu > :not(caption) > * > * { padding-top: 10px; padding-bottom: 10px; }
        .tabel-kartu > :not(caption) > * > *:first-child { padding-left: 20px; }
        .tabel-kartu > :not(caption) > * > *:last-child { padding-right: 20px; }
        .tabel-kartu thead th {
            background: #F7F9FB; color: var(--teks-muted); font-size: 12px; font-weight: 600;
            text-transform: uppercase; letter-spacing: .04em;
        }
        .tabel-kartu tbody tr:last-child > * { border-bottom: 0; }
        /* Jarak antarbagian halaman 20px */
        .jarak-bagian { margin-bottom: 20px; }
        .g-bagian { --bs-gutter-x: 20px; --bs-gutter-y: 20px; }

        /* ---------- Sidebar ---------- */
        .sidebar { width: 240px; background: var(--sidebar-bg); color: var(--sidebar-text); padding: 20px 14px; }
        .sidebar-nama { color: #fff; font-size: 16px; font-weight: 700; text-decoration: none; line-height: 1.25; }
        .sidebar-nama:hover { color: #fff; }
        .sidebar-sub { font-size: 12px; color: #9FB0C9; margin-top: 2px; }
        .sidebar-garis { border-color: rgba(255, 255, 255, .12); opacity: 1; margin: 16px 0; }
        .sidebar .nav-link {
            color: var(--sidebar-text); font-size: 14px; padding: 9px 12px;
            border-radius: 8px; margin-bottom: 2px;
        }
        .sidebar .nav-link:hover { color: #fff; background: rgba(255, 255, 255, .06); }
        .sidebar .nav-link.active { color: #fff; background: var(--sidebar-active); font-weight: 500; }
        .sidebar-pengguna { color: #fff; font-weight: 600; font-size: 14px; }
        .sidebar-peran { color: #9FB0C9; font-size: 12px; margin-bottom: 10px; }
        .btn-keluar {
            --bs-btn-color: var(--sidebar-text); --bs-btn-border-color: rgba(255, 255, 255, .25);
            --bs-btn-hover-color: #fff; --bs-btn-hover-bg: rgba(255, 255, 255, .08); --bs-btn-hover-border-color: rgba(255, 255, 255, .4);
            --bs-btn-active-color: #fff; --bs-btn-active-bg: rgba(255, 255, 255, .12);
        }

        /* Isi halaman. min-width: 0 supaya tabel lebar tidak mendorong tata letak keluar layar. */
        .isi-halaman { padding: 24px 28px; min-width: 0; }

        /* ================= Aturan seragam untuk seluruh halaman ================= */

        /* Keterangan singkat di bawah judul halaman */
        .subjudul { font-size: 14px; color: var(--teks-muted); }

        /* Formulir tidak melebar ke seluruh layar */
        .kartu-form { max-width: 640px; }

        /* Judul kolom tabel di semua halaman: huruf kapital kecil 12px, jarak antarhuruf, warna redup */
        .table > thead > tr > th {
            background: #F7F9FB; color: var(--teks-muted); font-size: 12px; font-weight: 600;
            text-transform: uppercase; letter-spacing: .04em; vertical-align: middle;
            border-bottom-color: var(--card-border);
        }

        /* Tabel yang langsung berada di dalam kartu: tepi kiri-kanan sejajar dengan padding kartu (20px) */
        .card > .table, .card > .table-responsive > .table { margin-bottom: 0; }
        .card > .table > :not(caption) > * > *,
        .card > .table-responsive > .table > :not(caption) > * > * { padding-top: 10px; padding-bottom: 10px; }
        .card > .table > :not(caption) > * > *:first-child,
        .card > .table-responsive > .table > :not(caption) > * > *:first-child { padding-left: 20px; }
        .card > .table > :not(caption) > * > *:last-child,
        .card > .table-responsive > .table > :not(caption) > * > *:last-child { padding-right: 20px; }
        .card > .table > tbody > tr:last-child > *,
        .card > .table-responsive > .table > tbody > tr:last-child > * { border-bottom: 0; }
        .card:has(> .table-responsive) { overflow: hidden; }
        /* Baris total di bagian bawah tabel */
        .table > tfoot > tr > * { border-top: 2px solid #AEB9C8; border-bottom: 0; font-weight: 700; }

        /* Tombol penyaring (Cari, Saring, Tampilkan, Reset): garis tepi dan teks --primary di atas latar putih.
           Saat disorot latarnya menjadi --primary dengan teks putih. */
        .btn-saring {
            --bs-btn-color: var(--primary); --bs-btn-bg: #fff; --bs-btn-border-color: var(--primary);
            --bs-btn-hover-color: #fff; --bs-btn-hover-bg: var(--primary); --bs-btn-hover-border-color: var(--primary);
            --bs-btn-active-color: #fff; --bs-btn-active-bg: var(--primary-gelap); --bs-btn-active-border-color: var(--primary-gelap);
            --bs-btn-focus-shadow-rgb: 31, 78, 121;
        }

        /* Keterangan tabel kosong, di tengah */
        /* Selektor dibuat lebih kuat dari aturan warna sel tabel bawaan Bootstrap */
        .table > tbody > tr > td.sel-kosong {
            text-align: center; color: var(--teks-muted); padding-top: 32px !important; padding-bottom: 32px !important;
        }

        /* Kotak pesan: warna kalem yang sama di seluruh halaman (berhasil, gagal, peringatan, informasi) */
        .alert { --bs-alert-border-radius: 8px; }
        .alert-success   { --bs-alert-bg: #EAF5EE; --bs-alert-border-color: #BFDCC8; --bs-alert-color: #1E5B35; --bs-alert-link-color: #1E5B35; }
        .alert-danger    { --bs-alert-bg: var(--danger-tipis); --bs-alert-border-color: #F2B8C6; --bs-alert-color: #8E0E2E; --bs-alert-link-color: #8E0E2E; }
        .alert-warning   { --bs-alert-bg: #FDF6E3; --bs-alert-border-color: #E8D9A8; --bs-alert-color: #7A5C18; --bs-alert-link-color: #7A5C18; }
        .alert-info      { --bs-alert-bg: #EDF2F8; --bs-alert-border-color: #C9D7E5; --bs-alert-color: #1F4E79; --bs-alert-link-color: #1F4E79; }
        .alert-secondary { --bs-alert-bg: #F4F6F9; --bs-alert-border-color: var(--card-border); --bs-alert-color: var(--teks); }

        /* Kepala jendela modal dengan warna status yang sama dengan kotak pesan */
        .modal-content { border-color: var(--card-border); border-radius: 12px; }
        .kepala-peringatan { background: #FDF6E3; color: #7A5C18; border-bottom-color: #E8D9A8; }
        .kepala-aman { background: #EAF5EE; color: #1E5B35; border-bottom-color: #BFDCC8; }
    </style>
