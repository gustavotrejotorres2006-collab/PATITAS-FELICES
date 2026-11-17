@extends('layouts.portal')

@section('title', 'Editar profesional')
@section('portal-section', 'Equipo')
@section('page-heading', 'Editar profesional')

@section('breadcrumbs')
    <nav aria-label="Ruta de navegación" class="mb-6 text-sm text-on-surface-variant">
        <a href="{{ route('portal.dashboard') }}" class="font-semibold text-primary">Inicio</a>
        <span aria-hidden="true"> / </span>
        <a href="{{ route('portal.profesionales.index') }}" class="font-semibold text-primary">Profesionales</a>
        <span aria-hidden="true"> / </span>
        <a href="{{ route('portal.profesionales.show', $profesional) }}" class="font-semibold text-primary">{{ $profesional->nombre }} {{ $profesional->apellido }}</a>
        <span aria-hidden="true"> / </span>
        <span aria-current="page">Editar</span>
    </nav>
@endsection

@section('content')
    <section class="mx-auto flex max-w-4xl flex-col gap-6">
        <div>
            <h2 class="text-2xl font-bold text-primary">Editar perfil y horario</h2>
            <p class="mt-1 text-on-surface-variant">Los días desactivados no estarán disponibles para agendar citas.</p>
        </div>
        <form method="POST" action="{{ route('portal.profesionales.update', $profesional) }}" class="flex flex-col gap-6 rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:p-7">
            @csrf
            @method('PUT')
            @include('portal.profesionales.partials.form')

            <fieldset class="flex flex-col gap-4 border-t border-outline-variant/40 pt-6">
                <legend class="px-1 text-lg font-bold text-primary">Horario semanal</legend>
                <p class="text-sm text-on-surface-variant">Activa los días de atención e indica el horario de inicio y fin.</p>
                <div class="flex flex-col gap-3">
                    @foreach ($dias as $numeroDia => $nombreDia)
                        @php
                            $actual = $horarios->get($numeroDia);
                            $activo = (bool) old("horarios.{$numeroDia}.activo", $actual?->activo ?? false);
                            $inicio = old("horarios.{$numeroDia}.hora_inicio", $actual ? substr($actual->hora_inicio, 0, 5) : '');
                            $fin = old("horarios.{$numeroDia}.hora_fin", $actual ? substr($actual->hora_fin, 0, 5) : '');
                        @endphp
                        <div class="grid gap-3 rounded-xl border border-outline-variant/50 p-4 sm:grid-cols-[minmax(8rem,0.7fr)_1fr_1fr] sm:items-end">
                            <label class="flex min-h-11 items-center gap-3 font-semibold text-primary">
                                <input type="hidden" name="horarios[{{ $numeroDia }}][activo]" value="0">
                                <input type="checkbox" name="horarios[{{ $numeroDia }}][activo]" value="1" @checked($activo) class="size-4 accent-primary">
                                {{ $nombreDia }}
                            </label>
                            <div class="flex flex-col gap-1">
                                <label for="inicio-{{ $numeroDia }}" class="text-sm font-medium text-on-surface-variant">Desde</label>
                                <input id="inicio-{{ $numeroDia }}" type="time" name="horarios[{{ $numeroDia }}][hora_inicio]" value="{{ $inicio }}" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error("horarios.{$numeroDia}.hora_inicio") aria-invalid="true" @enderror>
                                @error("horarios.{$numeroDia}.hora_inicio") <p class="text-sm text-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex flex-col gap-1">
                                <label for="fin-{{ $numeroDia }}" class="text-sm font-medium text-on-surface-variant">Hasta</label>
                                <input id="fin-{{ $numeroDia }}" type="time" name="horarios[{{ $numeroDia }}][hora_fin]" value="{{ $fin }}" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error("horarios.{$numeroDia}.hora_fin") aria-invalid="true" @enderror>
                                @error("horarios.{$numeroDia}.hora_fin") <p class="text-sm text-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </fieldset>

            <div class="flex flex-wrap justify-end gap-3 border-t border-outline-variant/40 pt-5">
                <a href="{{ route('portal.profesionales.show', $profesional) }}" class="inline-flex min-h-11 items-center rounded-lg px-4 font-semibold text-primary hover:bg-surface-container-low">Cancelar</a>
                <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-primary-container px-5 font-semibold text-on-primary hover:bg-primary">Guardar cambios</button>
            </div>
        </form>
    </section>
@endsection
