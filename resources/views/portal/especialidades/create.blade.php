@extends('layouts.portal')

@section('title', 'Nueva especialidad')
@section('portal-section', 'Equipo')
@section('page-heading', 'Nueva especialidad')

@section('breadcrumbs')
    <nav aria-label="Ruta de navegación" class="mb-6 text-sm text-on-surface-variant">
        <a href="{{ route('portal.dashboard') }}" class="font-semibold text-primary">Inicio</a>
        <span aria-hidden="true"> / </span>
        <a href="{{ route('portal.especialidades.index') }}" class="font-semibold text-primary">Especialidades</a>
        <span aria-hidden="true"> / </span>
        <span aria-current="page">Nueva</span>
    </nav>
@endsection

@section('content')
    <section class="mx-auto flex max-w-2xl flex-col gap-6">
        <div>
            <h2 class="text-2xl font-bold text-primary">Registrar especialidad</h2>
            <p class="mt-1 text-on-surface-variant">Agrega una nueva área clínica para asignarla a profesionales.</p>
        </div>
        <form method="POST" action="{{ route('portal.especialidades.store') }}" class="flex flex-col gap-6 rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:p-7">
            @csrf
            @include('portal.especialidades.partials.form')
            <div class="flex flex-wrap justify-end gap-3 border-t border-outline-variant/40 pt-5">
                <a href="{{ route('portal.especialidades.index') }}" class="inline-flex min-h-11 items-center rounded-lg px-4 font-semibold text-primary hover:bg-surface-container-low">Cancelar</a>
                <button type="submit" class="inline-flex min-h-11 items-center rounded-lg bg-primary-container px-5 font-semibold text-on-primary hover:bg-primary">Registrar especialidad</button>
            </div>
        </form>
    </section>
@endsection
