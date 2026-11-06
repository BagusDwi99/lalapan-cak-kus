<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Nama pendek 'peran' untuk middleware CekPeran.
        $middleware->alias([
            'peran' => \App\Http\Middleware\CekPeran::class,
        ]);

        // Tamu yang membuka halaman berlogin diarahkan ke /login,
        // pengguna yang sudah login dan membuka /login diarahkan ke /dasbor.
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/dasbor');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
