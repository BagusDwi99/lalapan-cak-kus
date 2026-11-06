@extends('layouts.app')

@section('judul', 'Tambah Menu')

@section('konten')
    <h1 class="h3 mb-3">Tambah Menu</h1>

    <div class="card" style="max-width: 760px;">
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
