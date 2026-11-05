<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Portal\MascotaController;
use App\Http\Controllers\Portal\PropietarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('public.home');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:Administrador'])
    ->prefix('portal')
    ->name('portal.')
    ->group(function (): void {
        Route::get('/', function () {
            return view('portal.dashboard');
        })->name('dashboard');

        Route::resource('propietarios', PropietarioController::class)->except(['destroy']);
        Route::get('mascotas', [MascotaController::class, 'index'])->name('mascotas.index');

        Route::get('propietarios/{propietario}/mascotas/create', [MascotaController::class, 'create'])
            ->name('propietarios.mascotas.create');
        Route::post('propietarios/{propietario}/mascotas', [MascotaController::class, 'store'])
            ->name('propietarios.mascotas.store');
    });
