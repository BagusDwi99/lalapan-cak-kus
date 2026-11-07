@extends('layouts.app')

@section('judul', 'Ubah Bahan Baku')

@section('konten')
    <x-kepala-halaman judul="Ubah Bahan Baku" keterangan="Stok dan harga rata-rata tidak bisa diubah langsung" />

    <div class="card kartu-form">
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
