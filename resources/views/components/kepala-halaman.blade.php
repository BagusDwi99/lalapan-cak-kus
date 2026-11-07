{{--
    Kepala halaman yang seragam: judul (24px) dan keterangan singkat di kiri,
    tombol aksi utama di kanan pada baris yang sama.

    Cara pakai:
        <x-kepala-halaman judul="Bahan Baku" keterangan="Daftar bahan baku beserta stoknya">
            <a href="/bahan-baku/create" class="btn btn-primary">Tambah</a>
        </x-kepala-halaman>

    Isi di antara tag (tombol) bersifat pilihan. Kelas tambahan, misalnya d-print-none,
    bisa ditulis langsung: <x-kepala-halaman class="d-print-none" ...>
--}}
@props(['judul', 'keterangan' => null])

<div {{ $attributes->merge(['class' => 'd-flex flex-wrap justify-content-between align-items-start gap-3 jarak-bagian']) }}>
    <div>
        <h1 class="h3 mb-1">{{ $judul }}</h1>
        @if ($keterangan)
            <div class="subjudul">{{ $keterangan }}</div>
        @endif
    </div>

    @if ($slot->isNotEmpty())
        <div class="d-flex flex-wrap align-items-start gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
