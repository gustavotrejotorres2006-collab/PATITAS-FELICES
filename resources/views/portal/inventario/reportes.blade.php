@extends('layouts.portal')

@section('title', 'Reporte de inventario')
@section('portal-section', 'Administración')
@section('page-heading', 'Reporte oficial de inventario')
@section('meta-description', 'Existencias y alertas de inventario emitidas para revisión administrativa.')

@section('breadcrumbs')
    <nav aria-label="Ruta de navegación" class="mb-6 flex items-center gap-2 text-sm text-on-surface-variant">
        <a href="{{ route('portal.inventario.index') }}" class="font-semibold text-primary underline">Inventario</a>
        <span aria-hidden="true">/</span>
        <span aria-current="page">Reporte oficial</span>
    </nav>
@endsection

@section('content')
    <section class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:p-6">
        <div>
            <h2 class="text-lg font-bold text-primary">Existencias al {{ now()->format('d/m/Y H:i') }}</h2>
            <p class="mt-1 text-sm text-on-surface-variant">Reporte consultado y registrado en la bitácora administrativa.</p>
        </div>
        <span class="rounded-full bg-surface-container px-3 py-1 text-sm font-semibold text-on-surface-variant">{{ $productos->count() }} productos</span>
    </section>

    @if ($productosCriticos->isNotEmpty())
        <section role="alert" class="mb-6 rounded-xl border border-error/30 bg-error-container px-5 py-4 text-sm text-on-error-container">
            <h2 class="font-bold">Vacunas en nivel crítico</h2>
            <p class="mt-1">{{ $productosCriticos->pluck('nombre')->join(', ') }}</p>
        </section>
    @endif

    <section class="overflow-hidden rounded-2xl border border-outline-variant/50 bg-surface-container-lowest shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[42rem] border-collapse text-left text-sm">
                <thead class="bg-surface-container-low text-xs uppercase tracking-wide text-on-surface-variant">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-semibold">Producto</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Código</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Categoría</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Existencia</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Stock mínimo</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Capacidad óptima</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/40">
                    @forelse ($productos as $producto)
                        <tr>
                            <td class="px-5 py-4 font-semibold text-on-surface">{{ $producto->nombre }}</td>
                            <td class="px-5 py-4 text-on-surface-variant">{{ $producto->codigo }}</td>
                            <td class="px-5 py-4 text-on-surface-variant">{{ $producto->vacuna?->nombre ?? $producto->categoria?->nombre ?? 'Sin categoría' }}</td>
                            <td class="px-5 py-4 font-semibold text-on-surface">{{ number_format((float) ($producto->stock_actual ?? 0), 3) }} {{ $producto->unidad_base }}</td>
                            <td class="px-5 py-4 text-on-surface-variant">{{ number_format((float) $producto->stock_minimo, 3) }}</td>
                            <td class="px-5 py-4 text-on-surface-variant">{{ $producto->stock_optimo !== null ? number_format((float) $producto->stock_optimo, 3) : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-on-surface-variant">No hay existencias registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
