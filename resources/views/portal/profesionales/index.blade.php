@extends('layouts.portal')

@section('title', 'Profesionales')
@section('portal-section', 'Equipo')
@section('page-heading', 'Profesionales')

@section('breadcrumbs')
    <nav aria-label="Ruta de navegación" class="mb-6 text-sm text-on-surface-variant">
        <a href="{{ route('portal.dashboard') }}" class="font-semibold text-primary">Inicio</a>
        <span aria-hidden="true"> / </span>
        <span aria-current="page">Profesionales</span>
    </nav>
@endsection

@section('portal-header-actions')
    <a href="{{ route('portal.profesionales.create') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-primary-container px-4 py-2 text-sm font-semibold text-on-primary hover:bg-primary">
        <span class="material-symbols-outlined text-lg" aria-hidden="true">person_add</span>
        Nuevo profesional
    </a>
@endsection

@section('content')
    <section class="flex flex-col gap-5">
        @if (session('success'))
            <p role="status" class="rounded-lg border border-secondary/30 bg-secondary-container/30 px-4 py-3 text-sm font-semibold text-primary">{{ session('success') }}</p>
        @endif
        <div>
            <h2 class="text-2xl font-bold text-primary">Equipo veterinario</h2>
            <p class="mt-1 text-on-surface-variant">Consulta perfiles, especialidades y horarios de atención.</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <form method="GET" action="{{ route('portal.profesionales.index') }}" role="search" class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row">
                <label for="buscar-profesional" class="sr-only">Buscar profesionales</label>
                <input id="buscar-profesional" name="buscar" type="search" value="{{ $search }}" maxlength="100" placeholder="Nombre, cédula o teléfono" class="min-h-12 flex-1 rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">
                <button type="submit" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-lg bg-primary-container px-5 py-3 font-semibold text-on-primary hover:bg-primary">
                    <span class="material-symbols-outlined" aria-hidden="true">search</span>
                    Buscar
                </button>
                @if ($search !== '')
                    <a href="{{ route('portal.profesionales.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-lg border border-outline-variant px-4 py-3 font-semibold text-primary">Limpiar</a>
                @endif
            </form>
            <a href="{{ route('portal.especialidades.index') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-lg border border-outline-variant px-4 py-3 text-sm font-semibold text-primary hover:bg-surface-container-low">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">clinical_notes</span>
                Especialidades
            </a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-outline-variant/50 bg-surface-container-lowest shadow-sm">
            @forelse ($profesionales as $profesional)
                <article class="grid gap-4 border-b border-outline-variant/40 px-5 py-4 last:border-b-0 md:grid-cols-[minmax(0,1.2fr)_minmax(0,1.3fr)_auto] md:items-center">
                    <div class="flex flex-col gap-1">
                        <a href="{{ route('portal.profesionales.show', $profesional) }}" class="font-bold text-primary hover:underline">{{ $profesional->nombre }} {{ $profesional->apellido }}</a>
                        <span class="text-sm text-on-surface-variant">{{ $profesional->cedula_profesional ?: 'Sin cédula registrada' }}</span>
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="flex flex-wrap gap-2">
                            @forelse ($profesional->especialidades as $especialidad)
                                <span class="rounded-full bg-surface-container px-3 py-1 text-xs font-semibold text-primary">{{ $especialidad->nombre }}</span>
                            @empty
                                <span class="text-sm text-on-surface-variant">Sin especialidades</span>
                            @endforelse
                        </div>
                        <span class="text-xs text-on-surface-variant">{{ $profesional->citas_count }} citas registradas</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="{{ $profesional->estado === 'activo' ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container text-on-surface-variant' }} rounded-full px-3 py-1 text-xs font-bold">
                            {{ $profesional->estado === 'activo' ? 'Activo' : 'Inactivo' }}
                        </span>
                        <a href="{{ route('portal.profesionales.edit', $profesional) }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg border border-outline-variant px-3 text-sm font-semibold text-primary hover:bg-surface-container-low">
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">edit</span>
                            Editar
                        </a>
                    </div>
                </article>
            @empty
                <div class="grid min-h-48 place-items-center px-6 py-10 text-center">
                    <div class="flex max-w-md flex-col items-center gap-3">
                        <span class="material-symbols-outlined text-4xl text-secondary" aria-hidden="true">medical_services</span>
                        <h3 class="font-bold text-primary">{{ $search !== '' ? 'No encontramos profesionales' : 'Aún no hay profesionales' }}</h3>
                        <p class="text-sm leading-6 text-on-surface-variant">{{ $search !== '' ? 'Prueba con otro nombre, cédula o teléfono.' : 'Registra al equipo para asignar especialidades y horarios.' }}</p>
                    </div>
                </div>
            @endforelse
        </div>

        {{ $profesionales->links() }}
    </section>
@endsection
