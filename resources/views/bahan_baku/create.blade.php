@extends('layouts.app')

@section('judul', 'Tambah Bahan Baku')

@section('konten')
    <h1 class="h3 mb-3">Tambah Bahan Baku</h1>

    <div class="card" style="max-width: 600px;">
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
