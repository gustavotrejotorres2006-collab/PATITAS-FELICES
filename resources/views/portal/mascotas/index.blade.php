@extends('layouts.portal')

@section('title', 'Mascotas')
@section('portal-section', 'Pacientes')
@section('page-heading', 'Mascotas')
@section('breadcrumbs')
    <nav aria-label="Ruta de navegación" class="mb-6 text-sm text-on-surface-variant">
        <a href="{{ route('portal.dashboard') }}" class="font-semibold text-primary">Inicio</a>
        <span aria-hidden="true"> / </span>
        <span aria-current="page">Mascotas</span>
    </nav>
@endsection

@section('content')
    <section class="flex flex-col gap-5">
        <div>
            <h2 class="text-2xl font-bold text-primary">Directorio de mascotas</h2>
            <p class="mt-2 text-on-surface-variant">Consulta pacientes y propietarios asociados.</p>
        </div>

        <form method="GET" action="{{ route('portal.mascotas.index') }}" role="search" class="flex flex-col gap-3 sm:flex-row">
            <label for="buscar" class="sr-only">Buscar mascotas</label>
            <input id="buscar" name="buscar" type="search" value="{{ $search }}" maxlength="100" placeholder="Nombre, especie, microchip o propietario" class="min-h-12 flex-1 rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">
            <button type="submit" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-lg bg-primary-container px-5 py-3 font-semibold text-on-primary hover:bg-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                <span class="material-symbols-outlined" aria-hidden="true">search</span>
                Buscar
            </button>
            @if ($search !== '')
                <a href="{{ route('portal.mascotas.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-lg border border-outline-variant px-4 py-3 font-semibold text-primary hover:bg-surface-container-low">Limpiar</a>
            @endif
        </form>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($mascotas as $mascota)
                <article class="flex flex-col gap-4 rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-bold text-primary">
                                <a href="{{ route('portal.mascotas.show', $mascota) }}" class="hover:underline">{{ $mascota->nombre }}</a>
                            </h3>
                            <p class="text-sm capitalize text-on-surface-variant">{{ $mascota->especie }}{{ $mascota->raza ? ' · '.$mascota->raza : '' }}</p>
                        </div>
                        <span class="rounded-full bg-surface-container px-3 py-1 text-xs font-semibold text-on-surface-variant">{{ $mascota->codigo }}</span>
                    </div>
                    <dl class="flex flex-col gap-2 border-t border-outline-variant/40 pt-3 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-on-surface-variant">Microchip</dt>
                            <dd class="break-all text-right font-medium text-on-surface">{{ $mascota->microchip ?: 'No registrado' }}</dd>
                        </div>
                        <div class="flex flex-col gap-1">
                            <dt class="text-on-surface-variant">Propietarios</dt>
                            <dd class="flex flex-col gap-1 font-medium text-primary">
                                @forelse ($mascota->propietarios as $propietario)
                                    <a href="{{ route('portal.propietarios.show', $propietario) }}" class="hover:underline">{{ $propietario->nombre }} {{ $propietario->apellido }}</a>
                                @empty
                                    <span class="text-on-surface-variant">Sin propietario asociado</span>
                                @endforelse
                            </dd>
                        </div>
                    </dl>
                    <a href="{{ route('portal.mascotas.edit', $mascota) }}" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-outline-variant px-3 text-sm font-semibold text-primary hover:bg-surface-container-low">
                        <span class="material-symbols-outlined text-lg" aria-hidden="true">edit</span>
                        Editar ficha
                    </a>
                </article>
            @empty
                <div class="grid min-h-48 place-items-center rounded-2xl border border-dashed border-outline-variant bg-surface-container-lowest px-6 py-10 text-center sm:col-span-2 xl:col-span-3">
                    <div class="flex max-w-md flex-col items-center gap-3">
                        <span class="material-symbols-outlined text-4xl text-secondary" aria-hidden="true">pets</span>
                        <h3 class="font-bold text-primary">{{ $search !== '' ? 'No encontramos mascotas' : 'Aún no hay mascotas' }}</h3>
                        <p class="text-sm leading-6 text-on-surface-variant">{{ $search !== '' ? 'Prueba con otro nombre, especie, microchip o propietario.' : 'Registra una mascota desde la ficha de su propietario.' }}</p>
                        <a href="{{ route('portal.propietarios.index') }}" class="font-semibold text-primary hover:underline">Ir a propietarios</a>
                    </div>
                </div>
            @endforelse
        </div>

        <div>{{ $mascotas->links() }}</div>
    </section>
@endsection
