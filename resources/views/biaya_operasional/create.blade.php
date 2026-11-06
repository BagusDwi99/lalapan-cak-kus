@extends('layouts.app')

@section('judul', 'Tambah Biaya Operasional')

@section('konten')
    <h1 class="h3 mb-3">Tambah Biaya Operasional</h1>

    <div class="card" style="max-width: 600px;">
        <div class="card-body">
            <form method="POST" action="/biaya-operasional">
                @csrf
                @include('biaya_operasional._form')

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="/biaya-operasional" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
@endsection
