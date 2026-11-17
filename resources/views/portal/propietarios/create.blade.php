@extends('layouts.portal')

@section('title', 'Nuevo propietario')
@section('portal-section', 'Directorio')
@section('page-heading', 'Nuevo propietario')
@section('breadcrumbs')
    <nav aria-label="Ruta de navegación" class="mb-6 text-sm text-on-surface-variant">
        <a href="{{ route('portal.dashboard') }}" class="font-semibold text-primary">Inicio</a>
        <span aria-hidden="true"> / </span>
        <a href="{{ route('portal.propietarios.index') }}" class="font-semibold text-primary">Propietarios</a>
        <span aria-hidden="true"> / </span>
        <span aria-current="page">Nuevo</span>
    </nav>
@endsection

@section('content')
    <section class="mx-auto flex max-w-3xl flex-col gap-6">
        <div>
            <h2 class="text-2xl font-bold text-primary">Registrar propietario</h2>
            <p class="mt-2 leading-7 text-on-surface-variant">El código del cliente se asignará automáticamente al guardar.</p>
        </div>
        <form method="POST" action="{{ route('portal.propietarios.store') }}" class="flex flex-col gap-6 rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:p-8">
            @csrf
            @include('portal.propietarios.partials.form')
            <div class="flex flex-wrap justify-end gap-3 border-t border-outline-variant/40 pt-5">
                <a href="{{ route('portal.propietarios.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-outline-variant px-4 py-2 font-semibold text-primary hover:bg-surface-container-low">Cancelar</a>
                <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-primary-container px-5 py-2 font-semibold text-on-primary hover:bg-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                    Guardar propietario
                </button>
            </div>
        </form>
    </section>
@endsection
