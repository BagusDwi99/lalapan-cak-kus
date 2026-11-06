<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — Warung Lalapan Cak Kus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    {{-- d-flex + align-items-center + justify-content-center membuat kotak login berada di tengah layar --}}
    <div class="d-flex align-items-center justify-content-center min-vh-100">
        <div class="card shadow-sm" style="width: 100%; max-width: 380px;">
            <div class="card-body p-4">
                <h1 class="h4 text-center mb-1">Warung Lalapan Cak Kus</h1>
                <p class="text-center text-muted mb-4">Silakan masuk untuk melanjutkan</p>

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

                    <button type="submit" class="btn btn-dark w-100">Masuk</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
