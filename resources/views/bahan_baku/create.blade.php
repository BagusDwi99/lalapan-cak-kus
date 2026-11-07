@extends('layouts.app')

@section('judul', 'Tambah Bahan Baku')

@section('konten')
    <x-kepala-halaman judul="Tambah Bahan Baku" keterangan="Stok awal hanya diisi sekali; selanjutnya stok berubah lewat barang masuk dan penjualan" />

    <div class="card kartu-form">
        <div class="card-body">
            <form method="POST" action="/bahan-baku">
                @csrf
                @include('bahan_baku._form')

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="/bahan-baku" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
@endsection
