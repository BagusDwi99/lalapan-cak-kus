@extends('layouts.app')

@section('judul', 'Ubah Bahan Baku')

@section('konten')
    <h1 class="h3 mb-3">Ubah Bahan Baku</h1>

    <div class="card" style="max-width: 600px;">
        <div class="card-body">
            <form method="POST" action="/bahan-baku/{{ $bahanBaku->id }}">
                @csrf
                {{-- Formulir HTML hanya mengenal GET dan POST. @method('PUT') memberi tahu
                     Laravel bahwa ini sebenarnya permintaan ubah data (PUT). --}}
                @method('PUT')
                @include('bahan_baku._form')

                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="/bahan-baku" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
@endsection
