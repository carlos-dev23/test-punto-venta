<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/inventario', function () {
        return view('inventario.index');
    })->name('inventario.index');

    Route::get('/ventas', function () {
        return view('ventas.index');
    })->name('ventas.index');

    Route::get('/ventas/notas', function () {
        return view('ventas.notas');
    })->name('ventas.notas');
});
