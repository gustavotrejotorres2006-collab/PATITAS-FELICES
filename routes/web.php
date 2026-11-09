<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Portal\AtencionController;
use App\Http\Controllers\Portal\CitaController;
use App\Http\Controllers\Portal\EspecialidadController;
use App\Http\Controllers\Portal\InventarioController;
use App\Http\Controllers\Portal\MascotaController;
use App\Http\Controllers\Portal\ProfesionalController;
use App\Http\Controllers\Portal\PropietarioController;
use App\Services\InventarioService;
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
        Route::get('/', function (InventarioService $inventarioService) {
            $productosCriticos = $inventarioService->criticalVaccineProducts();

            return view('portal.dashboard', compact('productosCriticos'));
        })->name('dashboard');

        Route::get('inventario', [InventarioController::class, 'index'])->name('inventario.index');
        Route::post('inventario/productos', [InventarioController::class, 'storeProduct'])->name('inventario.productos.store');
        Route::post('inventario/proveedores', [InventarioController::class, 'storeProvider'])->name('inventario.proveedores.store');
        Route::post('inventario/compras', [InventarioController::class, 'storePurchase'])->name('inventario.compras.store');
        Route::post('inventario/consumos', [InventarioController::class, 'storeConsumption'])->name('inventario.consumos.store');
        Route::get('inventario/reportes', [InventarioController::class, 'report'])->name('inventario.reportes');

        Route::resource('propietarios', PropietarioController::class)->except(['destroy']);
        Route::resource('mascotas', MascotaController::class)
            ->only(['index', 'show', 'edit', 'update']);
        Route::resource('profesionales', ProfesionalController::class)
            ->except(['destroy'])
            ->parameters(['profesionales' => 'profesional']);
        Route::resource('especialidades', EspecialidadController::class)
            ->except(['show', 'destroy'])
            ->parameters(['especialidades' => 'especialidad']);

        Route::get('propietarios/{propietario}/mascotas/create', [MascotaController::class, 'create'])
            ->name('propietarios.mascotas.create');
        Route::post('propietarios/{propietario}/mascotas', [MascotaController::class, 'store'])
            ->name('propietarios.mascotas.store');
    });

Route::middleware(['auth', 'role:Administrador,Recepcionista'])
    ->prefix('portal')
    ->name('portal.')
    ->group(function (): void {
        Route::resource('citas', CitaController::class)
            ->only(['index', 'create', 'store']);
        Route::patch('citas/{cita}/cancelar', [CitaController::class, 'cancel'])
            ->name('citas.cancelar');
    });

Route::middleware(['auth', 'role:Veterinario'])
    ->prefix('portal')
    ->name('portal.')
    ->group(function (): void {
        Route::get('atenciones', [AtencionController::class, 'index'])->name('atenciones.index');
        Route::get('atenciones/{cita}/create', [AtencionController::class, 'create'])->name('atenciones.create');
        Route::post('atenciones/{cita}', [AtencionController::class, 'store'])->name('atenciones.store');
        Route::get('expedientes/{atencion}', [AtencionController::class, 'show'])->name('atenciones.show');
        Route::post('expedientes/{atencion}/notas', [AtencionController::class, 'storeNote'])->name('atenciones.notas.store');
    });
