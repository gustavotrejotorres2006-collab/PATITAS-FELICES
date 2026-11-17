@extends('layouts.portal')

@section('title', 'Especialidades')
@section('portal-section', 'Equipo')
@section('page-heading', 'Especialidades')

@section('breadcrumbs')
    <nav aria-label="Ruta de navegación" class="mb-6 text-sm text-on-surface-variant">
        <a href="{{ route('portal.dashboard') }}" class="font-semibold text-primary">Inicio</a>
        <span aria-hidden="true"> / </span>
        <a href="{{ route('portal.profesionales.index') }}" class="font-semibold text-primary">Profesionales</a>
        <span aria-hidden="true"> / </span>
        <span aria-current="page">Especialidades</span>
    </nav>
@endsection

@section('portal-header-actions')
    <a href="{{ route('portal.especialidades.create') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-primary-container px-4 py-2 text-sm font-semibold text-on-primary hover:bg-primary">
        <span class="material-symbols-outlined text-lg" aria-hidden="true">add</span>
        Nueva especialidad
    </a>
@endsection

@section('content')
    <section class="flex flex-col gap-5">
        @if (session('success'))
            <p role="status" class="rounded-lg border border-secondary/30 bg-secondary-container/30 px-4 py-3 text-sm font-semibold text-primary">{{ session('success') }}</p>
        @endif
        <div>
            <h2 class="text-2xl font-bold text-primary">Especialidades clínicas</h2>
            <p class="mt-1 text-on-surface-variant">Administra las áreas de atención disponibles para el equipo.</p>
        </div>
        <form method="GET" action="{{ route('portal.especialidades.index') }}" role="search" class="flex flex-col gap-3 sm:flex-row">
            <label for="buscar-especialidad" class="sr-only">Buscar especialidades</label>
            <input id="buscar-especialidad" name="buscar" type="search" value="{{ $search }}" maxlength="100" placeholder="Nombre de especialidad" class="min-h-12 flex-1 rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">
            <button type="submit" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-lg bg-primary-container px-5 py-3 font-semibold text-on-primary hover:bg-primary">
                <span class="material-symbols-outlined" aria-hidden="true">search</span>
                Buscar
            </button>
            @if ($search !== '')
                <a href="{{ route('portal.especialidades.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-lg border border-outline-variant px-4 py-3 font-semibold text-primary">Limpiar</a>
            @endif
        </form>

        <div class="overflow-hidden rounded-2xl border border-outline-variant/50 bg-surface-container-lowest shadow-sm">
            @forelse ($especialidades as $especialidad)
                <article class="flex flex-col gap-3 border-b border-outline-variant/40 px-5 py-4 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-col gap-1">
                        <h3 class="font-bold text-primary">{{ $especialidad->nombre }}</h3>
                        <p class="text-sm text-on-surface-variant">{{ $especialidad->descripcion ?: 'Sin descripción' }}</p>
                        <span class="text-xs text-on-surface-variant">{{ $especialidad->profesionales_count }} profesionales asociados · {{ $especialidad->activo ? 'Activa' : 'Inactiva' }}</span>
                    </div>
                    <a href="{{ route('portal.especialidades.edit', $especialidad) }}" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-outline-variant px-3 text-sm font-semibold text-primary hover:bg-surface-container-low">
                        <span class="material-symbols-outlined text-lg" aria-hidden="true">edit</span>
                        Editar
                    </a>
                </article>
            @empty
                <div class="grid min-h-44 place-items-center px-6 py-10 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <h3 class="font-bold text-primary">{{ $search !== '' ? 'No encontramos especialidades' : 'Aún no hay especialidades' }}</h3>
                        <p class="text-sm text-on-surface-variant">{{ $search !== '' ? 'Prueba con otro término.' : 'Registra especialidades para asignarlas al equipo.' }}</p>
                    </div>
                </div>
            @endforelse
        </div>
        {{ $especialidades->links() }}
    </section>
@endsection
