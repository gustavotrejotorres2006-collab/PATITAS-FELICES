@extends('layouts.portal')

@section('title', 'Propietarios')
@section('portal-section', 'Directorio')
@section('page-heading', 'Propietarios')
@section('breadcrumbs')
    <nav aria-label="Ruta de navegación" class="mb-6 text-sm text-on-surface-variant">
        <a href="{{ route('portal.dashboard') }}" class="font-semibold text-primary">Inicio</a>
        <span aria-hidden="true"> / </span>
        <span aria-current="page">Propietarios</span>
    </nav>
@endsection

@section('portal-header-actions')
    <a href="{{ route('portal.propietarios.create') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-primary-container px-4 py-2 text-sm font-semibold text-on-primary transition-colors hover:bg-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
        <span class="material-symbols-outlined text-lg" aria-hidden="true">person_add</span>
        Nuevo propietario
    </a>
@endsection

@section('content')
    <section class="flex flex-col gap-5">
        <div class="flex flex-col gap-2">
            <h2 class="text-2xl font-bold text-primary">Directorio de propietarios</h2>
            <p class="text-on-surface-variant">Busca y administra los datos de contacto de tus clientes.</p>
        </div>

        <form method="GET" action="{{ route('portal.propietarios.index') }}" role="search" class="flex flex-col gap-3 sm:flex-row">
            <label for="buscar" class="sr-only">Buscar propietarios</label>
            <input id="buscar" name="buscar" type="search" value="{{ $search }}" maxlength="100" placeholder="Nombre, correo, teléfono o código" class="min-h-12 flex-1 rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">
            <button type="submit" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-lg bg-primary-container px-5 py-3 font-semibold text-on-primary transition-colors hover:bg-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                <span class="material-symbols-outlined" aria-hidden="true">search</span>
                Buscar
            </button>
            @if ($search !== '')
                <a href="{{ route('portal.propietarios.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-lg border border-outline-variant px-4 py-3 font-semibold text-primary hover:bg-surface-container-low">Limpiar</a>
            @endif
        </form>

        <div class="overflow-hidden rounded-2xl border border-outline-variant/50 bg-surface-container-lowest shadow-sm">
            <div class="hidden grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)_minmax(0,0.8fr)_auto] gap-4 border-b border-outline-variant/50 bg-surface-container-low px-5 py-3 text-xs font-bold uppercase tracking-wide text-on-surface-variant md:grid">
                <span>Propietario</span>
                <span>Contacto</span>
                <span>Mascotas</span>
                <span>Acciones</span>
            </div>

            @forelse ($propietarios as $propietario)
                <article class="grid gap-3 border-b border-outline-variant/40 px-5 py-4 last:border-b-0 md:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)_minmax(0,0.8fr)_auto] md:items-center md:gap-4">
                    <div class="flex flex-col gap-1">
                        <a href="{{ route('portal.propietarios.show', $propietario) }}" class="font-bold text-primary hover:underline">{{ $propietario->nombre }} {{ $propietario->apellido }}</a>
                        <span class="text-xs text-on-surface-variant">{{ $propietario->codigo }}</span>
                    </div>
                    <div class="flex flex-col gap-1 text-sm text-on-surface-variant">
                        <span>{{ $propietario->telefono ?: 'Sin teléfono' }}</span>
                        <span class="break-all">{{ $propietario->email ?: 'Sin correo' }}</span>
                    </div>
                    <p class="text-sm text-on-surface-variant">{{ $propietario->mascotas_count }} {{ $propietario->mascotas_count === 1 ? 'mascota' : 'mascotas' }}</p>
                    <a href="{{ route('portal.propietarios.edit', $propietario) }}" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-outline-variant px-3 text-sm font-semibold text-primary hover:bg-surface-container-low">
                        <span class="material-symbols-outlined text-lg" aria-hidden="true">edit</span>
                        Editar
                    </a>
                </article>
            @empty
                <div class="grid min-h-48 place-items-center px-6 py-10 text-center">
                    <div class="flex max-w-md flex-col items-center gap-3">
                        <span class="material-symbols-outlined text-4xl text-secondary" aria-hidden="true">group</span>
                        <h3 class="font-bold text-primary">{{ $search !== '' ? 'No encontramos propietarios' : 'Aún no hay propietarios' }}</h3>
                        <p class="text-sm leading-6 text-on-surface-variant">{{ $search !== '' ? 'Prueba con otro nombre, correo, teléfono o código.' : 'Registra al primer propietario para comenzar a organizar sus mascotas.' }}</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div>{{ $propietarios->links() }}</div>
    </section>
@endsection
