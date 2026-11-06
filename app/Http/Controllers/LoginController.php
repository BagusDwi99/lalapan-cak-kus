<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Mengatur proses masuk (login) dan keluar (logout).
class LoginController extends Controller
{
    // Menampilkan halaman formulir login.
    public function showForm()
    {
        return view('auth.login');
    }

    // Memeriksa email dan kata sandi yang dikirim dari formulir login.
    public function login(Request $request)
    {
        // Pastikan kedua isian tidak kosong dan email berformat benar.
        $kredensial = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // Auth::attempt mencocokkan email dan kata sandi dengan tabel users.
        // Hasilnya true jika cocok, dan pengguna langsung tercatat sudah masuk.
        if (Auth::attempt($kredensial)) {
            // Buat ulang ID sesi untuk mencegah pencurian sesi (session fixation).
            $request->session()->regenerate();

            // intended() mengarahkan ke halaman yang tadi ingin dibuka,
            // atau ke /dasbor jika tidak ada.
            return redirect()->intended('/dasbor');
        }

        // Jika gagal, kembali ke formulir dengan pesan kesalahan.
        // onlyInput('email') supaya email tetap terisi, kata sandi dikosongkan.
        return back()
            ->withErrors(['email' => 'Email atau kata sandi salah.'])
            ->onlyInput('email');
    }

    // Mengeluarkan pengguna dan menghapus sesinya.
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
