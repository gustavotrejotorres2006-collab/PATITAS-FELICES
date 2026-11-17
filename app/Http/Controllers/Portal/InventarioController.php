<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\CatalogoVacuna;
use App\Models\CategoriaProducto;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Services\InventarioService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventarioController extends Controller
{
    public function index(InventarioService $inventarioService): View
    {
        $productos = Producto::query()
            ->with(['categoria:id,nombre', 'vacuna:id,nombre'])
            ->withSum([
                'lotes as stock_actual' => fn (Builder $query) => $query
                    ->where('estado', 'disponible')
                    ->where(fn (Builder $query) => $query
                        ->whereNull('fecha_caducidad')
                        ->orWhereDate('fecha_caducidad', '>=', today())),
            ], 'cantidad_disponible')
            ->orderBy('nombre')
            ->paginate(15);
        $productosCriticos = $inventarioService->criticalVaccineProducts();
        $categorias = CategoriaProducto::query()->where('activo', true)->orderBy('nombre')->get();
        $vacunas = CatalogoVacuna::query()
            ->where('activo', true)
            ->whereNull('producto_id')
            ->orderBy('nombre')
            ->get();
        $proveedores = Proveedor::query()
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return view('portal.inventario.index', compact(
            'productos',
            'productosCriticos',
            'categorias',
            'vacunas',
            'proveedores',
        ));
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'categoria_id' => ['nullable', 'integer', Rule::exists('categorias_productos', 'id')->where('activo', true)],
            'codigo' => ['required', 'string', 'max:100', Rule::unique('productos', 'codigo')],
            'codigo_barras' => ['nullable', 'string', 'max:100', Rule::unique('productos', 'codigo_barras')],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'unidad_base' => ['required', 'string', 'max:50'],
            'costo_referencia' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'precio_venta' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'stock_minimo' => ['required', 'numeric', 'min:0', 'decimal:0,3'],
            'stock_optimo' => [
                Rule::requiredIf($request->filled('vacuna_id')),
                'nullable',
                'numeric',
                'gt:0',
                'decimal:0,3',
            ],
            'venta_publico' => ['required', 'boolean'],
            'uso_interno' => ['required', 'boolean'],
            'maneja_lote' => ['required', 'boolean'],
            'maneja_caducidad' => ['required', 'boolean'],
            'vacuna_id' => [
                'nullable',
                'integer',
                Rule::exists('catalogo_vacunas', 'id')
                    ->where('activo', true)
                    ->whereNull('producto_id'),
            ],
        ], [
            'stock_optimo.required' => 'Indica la capacidad óptima para generar alertas del inventario de vacunas.',
        ]);
        $vacunaId = $data['vacuna_id'] ?? null;
        unset($data['vacuna_id']);
        $data['estado'] = 'activo';

        DB::transaction(function () use ($data, $vacunaId): void {
            $producto = Producto::query()->create($data);

            if ($vacunaId !== null) {
                $linked = CatalogoVacuna::query()
                    ->whereKey($vacunaId)
                    ->whereNull('producto_id')
                    ->update(['producto_id' => $producto->id]);

                if ($linked !== 1) {
                    abort(409, 'La vacuna ya fue vinculada a otro producto.');
                }
            }
        });

        return redirect()
            ->route('portal.inventario.index')
            ->with('success', 'El producto se registró correctamente.');
    }

    public function storeProvider(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:2000'],
        ]);

        Proveedor::query()->create($data + ['estado' => 'activo']);

        return redirect()
            ->route('portal.inventario.index')
            ->with('success', 'El proveedor se registró correctamente.');
    }

    public function storePurchase(Request $request, InventarioService $inventarioService): RedirectResponse
    {
        $data = $request->validate([
            'proveedor_id' => [
                'nullable',
                'integer',
                Rule::exists('proveedores', 'id')->where('estado', 'activo'),
            ],
            'folio_proveedor' => ['nullable', 'string', 'max:100'],
            'fecha' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'detalles' => ['required', 'array', 'min:1', 'max:25'],
            'detalles.*.producto_id' => [
                'required',
                'integer',
                Rule::exists('productos', 'id')->where('estado', 'activo'),
            ],
            'detalles.*.cantidad' => ['required', 'numeric', 'gt:0', 'decimal:0,3'],
            'detalles.*.costo_unitario' => ['required', 'numeric', 'min:0', 'decimal:0,4'],
            'detalles.*.numero_lote' => ['nullable', 'string', 'max:255'],
            'detalles.*.fecha_caducidad' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        $inventarioService->receivePurchase($data, $data['detalles'], $request->user());

        return redirect()
            ->route('portal.inventario.index')
            ->with('success', 'La compra se recibió y las existencias quedaron actualizadas.');
    }

    public function storeConsumption(Request $request, InventarioService $inventarioService): RedirectResponse
    {
        $data = $request->validate([
            'producto_id' => [
                'required',
                'integer',
                Rule::exists('productos', 'id')->where('estado', 'activo'),
            ],
            'cantidad' => ['required', 'numeric', 'gt:0', 'decimal:0,3'],
            'motivo' => ['required', 'string', 'max:500'],
        ]);

        $producto = Producto::query()->findOrFail($data['producto_id']);
        $inventarioService->consume(
            $producto,
            (float) $data['cantidad'],
            $request->user(),
            $data['motivo'],
        );

        return redirect()
            ->route('portal.inventario.index')
            ->with('success', 'La salida de inventario se registró correctamente.');
    }

    public function report(Request $request, InventarioService $inventarioService): View
    {
        $productos = Producto::query()
            ->with(['categoria:id,nombre', 'vacuna:id,nombre'])
            ->withSum([
                'lotes as stock_actual' => fn (Builder $query) => $query
                    ->where('estado', 'disponible')
                    ->where(fn (Builder $query) => $query
                        ->whereNull('fecha_caducidad')
                        ->orWhereDate('fecha_caducidad', '>=', today())),
            ], 'cantidad_disponible')
            ->orderBy('nombre')
            ->get();
        $productosCriticos = $inventarioService->criticalVaccineProducts();

        Auditoria::query()->create([
            'user_id' => $request->user()->id,
            'accion' => 'reporte_inventario_consultado',
            'entidad' => 'reporte_inventario',
            'datos_nuevos' => [
                'productos' => $productos->count(),
                'vacunas_en_nivel_critico' => $productosCriticos->count(),
                'generado_at' => now()->toIso8601String(),
            ],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return view('portal.inventario.reportes', compact('productos', 'productosCriticos'));
    }
}
