@extends('layouts.portal')

@section('title', $mascota->nombre)
@section('portal-section', 'Pacientes')
@section('page-heading', $mascota->nombre)
@section('breadcrumbs')
    <nav aria-label="Ruta de navegación" class="mb-6 text-sm text-on-surface-variant">
        <a href="{{ route('portal.dashboard') }}" class="font-semibold text-primary">Inicio</a>
        <span aria-hidden="true"> / </span>
        <a href="{{ route('portal.mascotas.index') }}" class="font-semibold text-primary">Mascotas</a>
        <span aria-hidden="true"> / </span>
        <span aria-current="page">{{ $mascota->nombre }}</span>
    </nav>
@endsection

@section('portal-header-actions')
    <a href="{{ route('portal.mascotas.edit', $mascota) }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg border border-outline-variant px-4 py-2 text-sm font-semibold text-primary transition-colors hover:bg-surface-container-low">
        <span class="material-symbols-outlined text-lg" aria-hidden="true">edit</span>
        Editar ficha
    </a>
@endsection

@section('content')
    <section class="flex flex-col gap-6">
        @if (session('success'))
            <p role="status" class="rounded-xl border border-secondary/30 bg-secondary-container/40 px-4 py-3 font-medium text-on-secondary-container">{{ session('success') }}</p>
        @endif

        <article class="grid gap-5 rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:grid-cols-2 sm:p-6">
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Código de paciente</p>
                <p class="mt-1 font-semibold text-primary">{{ $mascota->codigo }}</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Especie y raza</p>
                <p class="mt-1 capitalize text-on-surface">{{ $mascota->especie }}{{ $mascota->raza ? ' · '.$mascota->raza : '' }}</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Sexo</p>
                <p class="mt-1 capitalize text-on-surface">{{ $mascota->sexo ? str_replace('_', ' ', $mascota->sexo) : 'No especificado' }}</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Fecha de nacimiento</p>
                <p class="mt-1 text-on-surface">{{ $mascota->fecha_nacimiento?->format('d/m/Y') ?? 'No registrada' }}</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Color</p>
                <p class="mt-1 text-on-surface">{{ $mascota->color ?: 'No registrado' }}</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Microchip</p>
                <p class="mt-1 break-all text-on-surface">{{ $mascota->microchip ?: 'No registrado' }}</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Esterilización</p>
                <p class="mt-1 text-on-surface">{{ $mascota->esterilizado ? 'Sí' : 'No' }}</p>
            </div>
            <div class="sm:col-span-2">
                <p class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Observaciones</p>
                <p class="mt-1 whitespace-pre-line text-on-surface">{{ $mascota->observaciones ?: 'Sin observaciones' }}</p>
            </div>
        </article>

        <section class="flex flex-col gap-4">
            <div>
                <h2 class="text-xl font-bold text-primary">Propietarios asociados</h2>
                <p class="mt-1 text-sm text-on-surface-variant">Clientes con vínculo activo a esta mascota.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                @forelse ($mascota->propietarios as $propietario)
                    <a href="{{ route('portal.propietarios.show', $propietario) }}" class="flex min-h-16 items-center justify-between gap-3 rounded-xl border border-outline-variant/50 bg-surface-container-lowest px-4 py-3 font-semibold text-primary hover:bg-surface-container-low">
                        <span>{{ $propietario->nombre }} {{ $propietario->apellido }}</span>
                        @if ($propietario->pivot->es_principal)
                            <span class="rounded-full bg-secondary-container px-3 py-1 text-xs text-on-secondary-container">Principal</span>
                        @endif
                    </a>
                @empty
                    <p class="rounded-xl border border-dashed border-outline-variant px-4 py-5 text-sm text-on-surface-variant">No hay propietarios activos asociados.</p>
                @endforelse
            </div>
        </section>
    </section>
@endsection
