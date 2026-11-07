@extends('layouts.app')

@section('judul', 'Tambah Menu')

@section('konten')
    <x-kepala-halaman judul="Tambah Menu" keterangan="Isi harga jual dan komposisi bahan baku untuk satu porsi" />

    <div class="card kartu-form">
        <div class="card-body">
            <form method="POST" action="/menu">
                @csrf
                @include('menu._form')

                <div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="/menu" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
