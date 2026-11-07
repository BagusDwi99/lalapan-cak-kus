<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — Warung Lalapan Cak Kus</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    {{-- Palet, huruf Inter, dan gaya kartu yang sama dengan halaman lain --}}
    @include('partials.gaya')
</head>
<body>
    {{-- d-flex + align-items-center + justify-content-center membuat kotak login berada di tengah layar.
         px-3: jarak tepi di layar ponsel. --}}
    <div class="d-flex align-items-center justify-content-center min-vh-100 px-3">
        <div style="width: 100%; max-width: 400px;">
            {{-- Nama warung di atas kartu --}}
            <div class="text-center mb-3">
                <div class="fw-bold" style="font-size: 18px;">Lalapan Cak Kus</div>
                <div class="subjudul" style="font-size: 12px;">Sistem Pencatatan Stok &amp; Laba</div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h1 class="h3 mb-1" style="font-size: 24px; font-weight: 600;">Masuk</h1>
                    <div class="subjudul mb-4">Gunakan email dan kata sandi akun Anda.</div>

                    <form method="POST" action="/login">
                        {{-- Token wajib untuk setiap formulir POST di Laravel (perlindungan CSRF) --}}
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror" autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Kata Sandi</label>
                            <input type="password" id="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Masuk</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
