@extends('layouts.portal')

@section('title', 'Perfil profesional')
@section('portal-section', 'Equipo')
@section('page-heading', 'Perfil profesional')

@section('breadcrumbs')
    <nav aria-label="Ruta de navegación" class="mb-6 text-sm text-on-surface-variant">
        <a href="{{ route('portal.dashboard') }}" class="font-semibold text-primary">Inicio</a>
        <span aria-hidden="true"> / </span>
        <a href="{{ route('portal.profesionales.index') }}" class="font-semibold text-primary">Profesionales</a>
        <span aria-hidden="true"> / </span>
        <span aria-current="page">{{ $profesional->nombre }} {{ $profesional->apellido }}</span>
    </nav>
@endsection

@section('portal-header-actions')
    <a href="{{ route('portal.profesionales.edit', $profesional) }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-primary-container px-4 py-2 text-sm font-semibold text-on-primary hover:bg-primary">
        <span class="material-symbols-outlined text-lg" aria-hidden="true">edit</span>
        Editar perfil y horario
    </a>
@endsection

@section('content')
    <section class="flex flex-col gap-6">
        @if (session('success'))
            <p role="status" class="rounded-lg border border-secondary/30 bg-secondary-container/30 px-4 py-3 text-sm font-semibold text-primary">{{ session('success') }}</p>
        @endif
        <article class="flex flex-col gap-5 rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:flex-row sm:items-start sm:justify-between sm:p-7">
            <div class="flex flex-col gap-2">
                <h2 class="text-2xl font-bold text-primary">{{ $profesional->nombre }} {{ $profesional->apellido }}</h2>
                <p class="text-on-surface-variant">{{ $profesional->cedula_profesional ?: 'Sin cédula registrada' }} · {{ $profesional->telefono ?: 'Sin teléfono' }}</p>
                <p class="text-sm text-on-surface-variant">
                    Cuenta clínica:
                    <span class="font-semibold text-primary">{{ $profesional->user?->email ?? 'Sin cuenta vinculada' }}</span>
                </p>
                <span class="{{ $profesional->estado === 'activo' ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container text-on-surface-variant' }} w-fit rounded-full px-3 py-1 text-xs font-bold">
                    {{ $profesional->estado === 'activo' ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
            <div class="flex flex-wrap gap-2">
                @forelse ($profesional->especialidades as $especialidad)
                    <span class="rounded-full bg-surface-container px-3 py-1 text-sm font-semibold text-primary">{{ $especialidad->nombre }}</span>
                @empty
                    <span class="text-sm text-on-surface-variant">Sin especialidades asignadas</span>
                @endforelse
            </div>
        </article>

        <section class="flex flex-col gap-4">
            <div>
                <h3 class="text-xl font-bold text-primary">Horario semanal</h3>
                <p class="mt-1 text-sm text-on-surface-variant">Días y horas en los que este profesional puede atender.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($dias as $numeroDia => $nombreDia)
                    @php($horario = $profesional->horarios->firstWhere('dia_semana', $numeroDia))
                    <article class="flex items-center justify-between gap-3 rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-4">
                        <span class="font-semibold text-primary">{{ $nombreDia }}</span>
                        <span class="text-sm {{ $horario?->activo ? 'text-on-surface' : 'text-on-surface-variant' }}">
                            {{ $horario?->activo ? substr($horario->hora_inicio, 0, 5).' – '.substr($horario->hora_fin, 0, 5) : 'Sin atención' }}
                        </span>
                    </article>
                @endforeach
            </div>
        </section>
    </section>
@endsection
