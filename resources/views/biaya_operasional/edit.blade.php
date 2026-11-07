@extends('layouts.app')

@section('judul', 'Ubah Biaya Operasional')

@section('konten')
    <x-kepala-halaman judul="Ubah Biaya Operasional" keterangan="Nama biaya tidak boleh sama dalam satu periode" />

    <div class="card kartu-form">
        <div class="card-body">
            <form method="POST" action="/biaya-operasional/{{ $biaya->id }}">
                @csrf
                @method('PUT')
                @include('biaya_operasional._form')

                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="/biaya-operasional" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
@endsection
