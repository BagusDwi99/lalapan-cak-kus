{{-- Halaman dasbor memakai kerangka layouts/app.blade.php --}}
@extends('layouts.app')

@section('judul', 'Dasbor')

@section('konten')
    <h1 class="h3 mb-3">Dasbor</h1>
    <p>Selamat datang, {{ auth()->user()->name }}.</p>

    {{-- Lencana peringatan, hanya tampil jika ada bahan baku yang stoknya menipis --}}
    @if ($jumlahStokMenipis > 0)
        <a href="/bahan-baku" class="badge text-bg-danger fs-6 text-decoration-none p-2">
            Peringatan: {{ $jumlahStokMenipis }} bahan baku stoknya menipis
        </a>
    @endif
@endsection
