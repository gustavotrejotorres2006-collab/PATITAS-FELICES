@extends('layouts.portal')

@section('title', 'Control de inventario')
@section('portal-section', 'Administración')
@section('page-heading', 'Control de inventario')
@section('meta-description', 'Administra productos, compras, salidas y existencias de la clínica.')

@section('breadcrumbs')
    <nav aria-label="Ruta de navegación" class="mb-6 text-sm text-on-surface-variant">
        <span aria-current="page" class="font-semibold text-primary">Inventario</span>
    </nav>
@endsection

@section('portal-header-actions')
    <a href="{{ route('portal.inventario.reportes') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-primary-container px-4 py-2 text-sm font-semibold text-on-primary hover:bg-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
        <span class="material-symbols-outlined text-lg" aria-hidden="true">summarize</span>
        Reporte oficial
    </a>
@endsection

@section('content')
    @if (session('success'))
        <p role="status" class="mb-5 rounded-xl border border-secondary/30 bg-secondary-container/40 px-4 py-3 text-sm font-semibold text-on-secondary-container">
            {{ session('success') }}
        </p>
    @endif
    @if ($errors->any())
        <div role="alert" class="mb-5 rounded-xl border border-error/30 bg-error-container px-4 py-3 text-sm text-on-error-container">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($productosCriticos->isNotEmpty())
        <section role="alert" class="mb-6 rounded-2xl border border-error/30 bg-error-container p-5 text-on-error-container shadow-sm">
            <h2 class="font-bold">Alerta crítica de vacunas</h2>
            <p class="mt-1 text-sm leading-6">
                El inventario de estos productos está al 15% o menos de su capacidad óptima:
                {{ $productosCriticos->pluck('nombre')->join(', ') }}.
            </p>
        </section>
    @endif

    <section aria-label="Existencias actuales" class="overflow-hidden rounded-2xl border border-outline-variant/50 bg-surface-container-lowest shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-outline-variant/40 px-5 py-4 sm:px-6">
            <div>
                <h2 class="font-bold text-primary">Existencias actuales</h2>
                <p class="mt-1 text-sm text-on-surface-variant">Las salidas consumen primero el lote con caducidad más próxima.</p>
            </div>
            <span class="rounded-full bg-surface-container px-3 py-1 text-xs font-semibold text-on-surface-variant">{{ $productos->total() }} productos</span>
        </div>
        @if ($productos->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-on-surface-variant">Todavía no hay productos registrados.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[48rem] border-collapse text-left text-sm">
                    <thead class="bg-surface-container-low text-xs uppercase tracking-wide text-on-surface-variant">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold">Producto</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Código</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Existencia</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Mínimo</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/40">
                        @foreach ($productos as $producto)
                            @php($stockActual = (float) ($producto->stock_actual ?? 0))
                            @php($esCritico = $productosCriticos->contains('id', $producto->id))
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-on-surface">{{ $producto->nombre }}</p>
                                    <p class="mt-1 text-xs text-on-surface-variant">{{ $producto->vacuna?->nombre ?? $producto->categoria?->nombre ?? 'Sin categoría' }}</p>
                                </td>
                                <td class="px-5 py-4 text-on-surface-variant">{{ $producto->codigo }}</td>
                                <td class="px-5 py-4 font-semibold text-on-surface">{{ number_format($stockActual, 3) }} {{ $producto->unidad_base }}</td>
                                <td class="px-5 py-4 text-on-surface-variant">{{ number_format((float) $producto->stock_minimo, 3) }} {{ $producto->unidad_base }}</td>
                                <td class="px-5 py-4">
                                    @if ($esCritico)
                                        <span class="rounded-full bg-error-container px-3 py-1 text-xs font-semibold text-on-error-container">Vacuna en nivel crítico</span>
                                    @elseif ($stockActual <= (float) $producto->stock_minimo)
                                        <span class="rounded-full bg-error-container/70 px-3 py-1 text-xs font-semibold text-on-error-container">Stock bajo</span>
                                    @else
                                        <span class="rounded-full bg-secondary-container px-3 py-1 text-xs font-semibold text-on-secondary-container">Disponible</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-outline-variant/40 px-5 py-4">{{ $productos->links() }}</div>
        @endif
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-2">
        <article class="rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:p-6">
            <h2 class="text-lg font-bold text-primary">Registrar producto</h2>
            <p class="mt-1 text-sm text-on-surface-variant">Vincula las vacunas clínicas con su producto de inventario para descontar existencias al aplicarlas.</p>
            <form method="POST" action="{{ route('portal.inventario.productos.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                @csrf
                <div class="flex flex-col gap-2">
                    <label for="codigo" class="text-sm font-semibold text-primary">Código</label>
                    <input id="codigo" name="codigo" value="{{ old('codigo') }}" required maxlength="100" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                </div>
                <div class="flex flex-col gap-2">
                    <label for="nombre" class="text-sm font-semibold text-primary">Nombre</label>
                    <input id="nombre" name="nombre" value="{{ old('nombre') }}" required maxlength="255" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                </div>
                <div class="flex flex-col gap-2">
                    <label for="categoria_id" class="text-sm font-semibold text-primary">Categoría</label>
                    <select id="categoria_id" name="categoria_id" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                        <option value="">Sin categoría</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected(old('categoria_id') == $categoria->id)>{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="unidad_base" class="text-sm font-semibold text-primary">Unidad base</label>
                    <input id="unidad_base" name="unidad_base" value="{{ old('unidad_base', 'pieza') }}" required maxlength="50" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                </div>
                <div class="flex flex-col gap-2">
                    <label for="costo_referencia" class="text-sm font-semibold text-primary">Costo de referencia</label>
                    <input id="costo_referencia" name="costo_referencia" type="number" min="0" step="0.01" value="{{ old('costo_referencia', '0.00') }}" required class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                </div>
                <div class="flex flex-col gap-2">
                    <label for="precio_venta" class="text-sm font-semibold text-primary">Precio de venta</label>
                    <input id="precio_venta" name="precio_venta" type="number" min="0" step="0.01" value="{{ old('precio_venta', '0.00') }}" required class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                </div>
                <div class="flex flex-col gap-2">
                    <label for="stock_minimo" class="text-sm font-semibold text-primary">Stock mínimo</label>
                    <input id="stock_minimo" name="stock_minimo" type="number" min="0" step="0.001" value="{{ old('stock_minimo', '0') }}" required class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                </div>
                <div class="flex flex-col gap-2">
                    <label for="stock_optimo" class="text-sm font-semibold text-primary">Capacidad óptima</label>
                    <input id="stock_optimo" name="stock_optimo" type="number" min="0.001" step="0.001" value="{{ old('stock_optimo') }}" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                </div>
                <div class="flex flex-col gap-2 sm:col-span-2">
                    <label for="vacuna_id" class="text-sm font-semibold text-primary">Vincular al catálogo clínico de vacunas (opcional)</label>
                    <select id="vacuna_id" name="vacuna_id" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                        <option value="">No es una vacuna clínica</option>
                        @foreach ($vacunas as $vacuna)
                            <option value="{{ $vacuna->id }}" @selected(old('vacuna_id') == $vacuna->id)>{{ $vacuna->nombre }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-on-surface-variant">Las vacunas requieren capacidad óptima para activar la alerta al 15%.</p>
                </div>
                <div class="grid gap-2 sm:col-span-2 sm:grid-cols-2">
                    @foreach ([
                        'venta_publico' => 'Disponible para venta al público',
                        'uso_interno' => 'Uso interno de la clínica',
                        'maneja_lote' => 'Requiere número de lote al recibir',
                        'maneja_caducidad' => 'Requiere fecha de caducidad al recibir',
                    ] as $field => $label)
                        <label class="flex items-center gap-3 rounded-lg bg-surface-container-low px-3 py-2 text-sm text-on-surface">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, in_array($field, ['venta_publico', 'maneja_lote', 'maneja_caducidad'], true))) class="size-4 accent-primary">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <button type="submit" class="min-h-11 rounded-lg bg-primary-container px-4 py-2 text-sm font-semibold text-on-primary hover:bg-primary sm:col-span-2">Guardar producto</button>
            </form>
        </article>

        <div class="flex flex-col gap-6">
            <article class="rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:p-6">
                <h2 class="text-lg font-bold text-primary">Registrar proveedor</h2>
                <form method="POST" action="{{ route('portal.inventario.proveedores.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                    @csrf
                    <label class="flex flex-col gap-2 text-sm font-semibold text-primary sm:col-span-2">
                        Nombre
                        <input name="nombre" value="{{ old('nombre') }}" required maxlength="255" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 font-normal text-on-surface">
                    </label>
                    <label class="flex flex-col gap-2 text-sm font-semibold text-primary">
                        Teléfono
                        <input name="telefono" value="{{ old('telefono') }}" maxlength="50" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 font-normal text-on-surface">
                    </label>
                    <label class="flex flex-col gap-2 text-sm font-semibold text-primary">
                        Correo
                        <input name="email" type="email" value="{{ old('email') }}" maxlength="255" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 font-normal text-on-surface">
                    </label>
                    <button type="submit" class="min-h-11 rounded-lg border border-outline px-4 py-2 text-sm font-semibold text-primary hover:bg-surface-container-low sm:col-span-2">Guardar proveedor</button>
                </form>
            </article>

            <article class="rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:p-6">
                <h2 class="text-lg font-bold text-primary">Recibir compra</h2>
                <p class="mt-1 text-sm text-on-surface-variant">Cada recepción crea un lote y un movimiento auditable de entrada.</p>
                <form method="POST" action="{{ route('portal.inventario.compras.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                    @csrf
                    <label class="flex flex-col gap-2 text-sm font-semibold text-primary">
                        Proveedor
                        <select name="proveedor_id" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 font-normal text-on-surface">
                            <option value="">Sin proveedor</option>
                            @foreach ($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="flex flex-col gap-2 text-sm font-semibold text-primary">
                        Fecha
                        <input name="fecha" type="date" value="{{ old('fecha', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 font-normal text-on-surface">
                    </label>
                    <label class="flex flex-col gap-2 text-sm font-semibold text-primary sm:col-span-2">
                        Producto
                        <select name="detalles[0][producto_id]" required class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 font-normal text-on-surface">
                            <option value="">Selecciona un producto</option>
                            @foreach ($productos as $producto)
                                <option value="{{ $producto->id }}">{{ $producto->codigo }} · {{ $producto->nombre }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="flex flex-col gap-2 text-sm font-semibold text-primary">
                        Cantidad
                        <input name="detalles[0][cantidad]" type="number" min="0.001" step="0.001" required class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 font-normal text-on-surface">
                    </label>
                    <label class="flex flex-col gap-2 text-sm font-semibold text-primary">
                        Costo unitario
                        <input name="detalles[0][costo_unitario]" type="number" min="0" step="0.0001" required class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 font-normal text-on-surface">
                    </label>
                    <label class="flex flex-col gap-2 text-sm font-semibold text-primary">
                        Número de lote
                        <input name="detalles[0][numero_lote]" maxlength="255" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 font-normal text-on-surface">
                    </label>
                    <label class="flex flex-col gap-2 text-sm font-semibold text-primary">
                        Fecha de caducidad
                        <input name="detalles[0][fecha_caducidad]" type="date" min="{{ now()->toDateString() }}" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 font-normal text-on-surface">
                    </label>
                    <label class="flex flex-col gap-2 text-sm font-semibold text-primary sm:col-span-2">
                        Observaciones
                        <textarea name="observaciones" rows="2" maxlength="2000" class="rounded-lg border border-outline-variant bg-surface px-3 py-2 font-normal text-on-surface"></textarea>
                    </label>
                    <button type="submit" class="min-h-11 rounded-lg bg-primary-container px-4 py-2 text-sm font-semibold text-on-primary hover:bg-primary sm:col-span-2">Recibir y actualizar existencias</button>
                </form>
            </article>

            <article class="rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:p-6">
                <h2 class="text-lg font-bold text-primary">Registrar consumo</h2>
                <p class="mt-1 text-sm text-on-surface-variant">El sistema asigna las unidades de los lotes con caducidad más próxima y no permite salidas superiores a las existencias.</p>
                <form method="POST" action="{{ route('portal.inventario.consumos.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                    @csrf
                    <label class="flex flex-col gap-2 text-sm font-semibold text-primary sm:col-span-2">
                        Producto
                        <select name="producto_id" required class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 font-normal text-on-surface">
                            <option value="">Selecciona un producto</option>
                            @foreach ($productos as $producto)
                                <option value="{{ $producto->id }}">{{ $producto->codigo }} · {{ $producto->nombre }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="flex flex-col gap-2 text-sm font-semibold text-primary">
                        Cantidad
                        <input name="cantidad" type="number" min="0.001" step="0.001" required class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 font-normal text-on-surface">
                    </label>
                    <label class="flex flex-col gap-2 text-sm font-semibold text-primary">
                        Motivo
                        <input name="motivo" maxlength="500" required class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 font-normal text-on-surface">
                    </label>
                    <button type="submit" class="min-h-11 rounded-lg border border-outline px-4 py-2 text-sm font-semibold text-primary hover:bg-surface-container-low sm:col-span-2">Registrar salida</button>
                </form>
            </article>
        </div>
    </section>
@endsection
