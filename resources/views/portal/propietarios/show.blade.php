@extends('layouts.portal')

@section('title', $propietario->nombre.' '.$propietario->apellido)
@section('portal-section', 'Directorio')
@section('page-heading', $propietario->nombre.' '.$propietario->apellido)
@section('breadcrumbs')
    <nav aria-label="Ruta de navegación" class="mb-6 text-sm text-on-surface-variant">
        <a href="{{ route('portal.dashboard') }}" class="font-semibold text-primary">Inicio</a>
        <span aria-hidden="true"> / </span>
        <a href="{{ route('portal.propietarios.index') }}" class="font-semibold text-primary">Propietarios</a>
        <span aria-hidden="true"> / </span>
        <span aria-current="page">{{ $propietario->nombre }} {{ $propietario->apellido }}</span>
    </nav>
@endsection

@section('portal-header-actions')
    <a href="{{ route('portal.propietarios.edit', $propietario) }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg border border-outline-variant px-4 py-2 text-sm font-semibold text-primary transition-colors hover:bg-surface-container-low">
        <span class="material-symbols-outlined text-lg" aria-hidden="true">edit</span>
        Editar
    </a>
@endsection

@section('content')
    <section class="flex flex-col gap-6">
        @if (session('success'))
            <p role="status" class="rounded-xl border border-secondary/30 bg-secondary-container/40 px-4 py-3 font-medium text-on-secondary-container">{{ session('success') }}</p>
        @endif

        <article class="grid gap-5 rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:grid-cols-2 sm:p-6">
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Código de cliente</p>
                <p class="mt-1 font-semibold text-primary">{{ $propietario->codigo }}</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Teléfono</p>
                <p class="mt-1 text-on-surface">{{ $propietario->telefono ?: 'No registrado' }}</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Correo electrónico</p>
                <p class="mt-1 break-all text-on-surface">{{ $propietario->email ?: 'No registrado' }}</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Dirección</p>
                <p class="mt-1 whitespace-pre-line text-on-surface">{{ $propietario->direccion ?: 'No registrada' }}</p>
            </div>
        </article>

        <section class="flex flex-col gap-4">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-primary">Mascotas</h2>
                    <p class="mt-1 text-sm text-on-surface-variant">Mascotas vinculadas a este propietario.</p>
                </div>
                <a href="{{ route('portal.propietarios.mascotas.create', $propietario) }}" class="inline-flex min-h-11 items-center gap-2 rounded-lg bg-primary-container px-4 py-2 text-sm font-semibold text-on-primary hover:bg-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                    <span class="material-symbols-outlined text-lg" aria-hidden="true">add</span>
                    Registrar mascota
                </a>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($propietario->mascotas as $mascota)
                    <article class="flex flex-col gap-3 rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-lg font-bold text-primary">{{ $mascota->nombre }}</h3>
                            <span class="rounded-full bg-surface-container px-3 py-1 text-xs font-semibold text-on-surface-variant">{{ $mascota->codigo }}</span>
                        </div>
                        <p class="text-sm capitalize text-on-surface-variant">{{ $mascota->especie }}{{ $mascota->raza ? ' · '.$mascota->raza : '' }}</p>
                        <p class="text-sm text-on-surface-variant">{{ $mascota->microchip ? 'Microchip: '.$mascota->microchip : 'Sin microchip registrado' }}</p>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-outline-variant bg-surface-container-lowest px-5 py-8 text-center sm:col-span-2 xl:col-span-3">
                        <p class="font-semibold text-primary">Todavía no hay mascotas registradas para este propietario.</p>
                        <p class="mt-1 text-sm text-on-surface-variant">Usa “Registrar mascota” para agregar la primera.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </section>
@endsection
