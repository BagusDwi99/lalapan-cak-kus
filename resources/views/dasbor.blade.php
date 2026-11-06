{{-- Halaman dasbor memakai kerangka layouts/app.blade.php --}}
@extends('layouts.app')

@section('judul', 'Dasbor')

@section('konten')
    <h1 class="h3 mb-3">Dasbor</h1>
    <p>Selamat datang, {{ auth()->user()->name }}.</p>
@endsection
