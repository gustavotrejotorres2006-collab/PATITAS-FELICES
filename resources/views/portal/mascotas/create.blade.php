@extends('layouts.portal')

@section('title', 'Registrar mascota')
@section('portal-section', 'Pacientes')
@section('page-heading', 'Registrar mascota')
@section('breadcrumbs')
    <nav aria-label="Ruta de navegación" class="mb-6 text-sm text-on-surface-variant">
        <a href="{{ route('portal.dashboard') }}" class="font-semibold text-primary">Inicio</a>
        <span aria-hidden="true"> / </span>
        <a href="{{ route('portal.propietarios.index') }}" class="font-semibold text-primary">Propietarios</a>
        <span aria-hidden="true"> / </span>
        <a href="{{ route('portal.propietarios.show', $propietario) }}" class="font-semibold text-primary">{{ $propietario->nombre }} {{ $propietario->apellido }}</a>
        <span aria-hidden="true"> / </span>
        <span aria-current="page">Nueva mascota</span>
    </nav>
@endsection

@section('content')
    <section class="mx-auto flex max-w-3xl flex-col gap-6">
        <div>
            <h2 class="text-2xl font-bold text-primary">Registrar mascota</h2>
            <p class="mt-2 leading-7 text-on-surface-variant">Propietario principal: <span class="font-semibold text-primary">{{ $propietario->nombre }} {{ $propietario->apellido }}</span>. El código de paciente se asignará automáticamente.</p>
        </div>

        <form method="POST" action="{{ route('portal.propietarios.mascotas.store', $propietario) }}" class="flex flex-col gap-6 rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:p-8">
            @csrf
            @include('portal.mascotas.partials.form')

            <div class="flex flex-wrap justify-end gap-3 border-t border-outline-variant/40 pt-5">
                <a href="{{ route('portal.propietarios.show', $propietario) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-outline-variant px-4 py-2 font-semibold text-primary hover:bg-surface-container-low">Cancelar</a>
                <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-primary-container px-5 py-2 font-semibold text-on-primary hover:bg-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                    Guardar mascota
                </button>
            </div>
        </form>
    </section>
@endsection
