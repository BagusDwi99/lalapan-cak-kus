@extends('layouts.app')

@section('judul', 'Ubah Menu')

@section('konten')
    <x-kepala-halaman judul="Ubah Menu" keterangan="Isi harga jual dan komposisi bahan baku untuk satu porsi" />

    <div class="card kartu-form">
        <div class="card-body">
            <form method="POST" action="/menu/{{ $menu->id }}">
                @csrf
                @method('PUT')
                @include('menu._form')

                <div>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="/menu" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
