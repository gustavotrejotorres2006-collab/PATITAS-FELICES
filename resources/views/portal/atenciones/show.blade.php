@extends('layouts.portal')

@section('title', 'Expediente clínico')
@section('portal-section', 'Módulo médico')
@section('page-heading', 'Expediente clínico')
@section('meta-description', 'Consulta el expediente médico, las notas y el historial de la mascota.')

@section('breadcrumbs')
    <nav aria-label="Ruta de navegación" class="mb-6 flex items-center gap-2 text-sm text-on-surface-variant">
        <a href="{{ route('portal.atenciones.index') }}" class="font-semibold text-primary underline">Atención clínica</a>
        <span aria-hidden="true">/</span>
        <span aria-current="page">{{ $atencion->folio }}</span>
    </nav>
@endsection

@section('content')
    @if (session('success'))
        <p role="status" class="mb-5 rounded-xl border border-secondary/30 bg-secondary-container/40 px-4 py-3 text-sm font-semibold text-on-secondary-container">
            {{ session('success') }}
        </p>
    @endif
    @if ($errors->any())
        <div role="alert" class="mb-5 rounded-xl border border-error/30 bg-error-container px-4 py-3 text-sm text-on-error-container">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="flex flex-col gap-5 rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:p-7">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">{{ $atencion->folio }} · Cita {{ $atencion->cita?->folio ?? 'sin cita' }}</p>
                <h2 class="mt-2 text-2xl font-bold text-primary">{{ $atencion->mascota->nombre }}</h2>
                <p class="mt-1 text-sm text-on-surface-variant">
                    {{ ucfirst($atencion->mascota->especie) }} · {{ $atencion->propietario?->nombre ?? 'Sin propietario' }} · {{ $atencion->fecha_atencion->format('d/m/Y H:i') }}
                </p>
            </div>
            @if ($atencion->hospitalizacion_recomendada)
                <span class="inline-flex items-center gap-2 rounded-full bg-error-container px-3 py-2 text-sm font-semibold text-on-error-container">
                    <span class="material-symbols-outlined text-lg" aria-hidden="true">local_hospital</span>
                    Hospitalización recomendada
                </span>
            @endif
        </div>
        <div class="grid gap-4 border-t border-outline-variant/40 pt-5 sm:grid-cols-2">
            <div>
                <h3 class="text-sm font-bold text-primary">Motivo de consulta</h3>
                <p class="mt-1 text-sm leading-6 text-on-surface">{{ $atencion->motivo ?: 'Sin motivo registrado' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-bold text-primary">Diagnóstico</h3>
                @foreach ($atencion->diagnosticos as $diagnostico)
                    <p class="mt-1 text-sm leading-6 text-on-surface">{{ $diagnostico->diagnostico?->nombre ?? $diagnostico->diagnostico_personalizado }}</p>
                    @if ($diagnostico->observaciones)
                        <p class="text-sm text-on-surface-variant">{{ $diagnostico->observaciones }}</p>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-2">
        <article class="rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:p-6">
            <h2 class="text-lg font-bold text-primary">Signos vitales</h2>
            @forelse ($atencion->signosVitales as $signos)
                <dl class="mt-4 grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                    @foreach ([
                        'Peso' => $signos->peso_kg ? $signos->peso_kg.' kg' : null,
                        'Temperatura' => $signos->temperatura_c ? $signos->temperatura_c.' °C' : null,
                        'Frecuencia cardiaca' => $signos->frecuencia_cardiaca ? $signos->frecuencia_cardiaca.' lpm' : null,
                        'Frecuencia respiratoria' => $signos->frecuencia_respiratoria ? $signos->frecuencia_respiratoria.' rpm' : null,
                        'Presión arterial' => $signos->presion_arterial,
                        'Condición corporal' => $signos->condicion_corporal,
                    ] as $label => $value)
                        @if ($value !== null)
                            <div>
                                <dt class="text-xs font-semibold text-on-surface-variant">{{ $label }}</dt>
                                <dd class="mt-1 font-semibold text-on-surface">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
                @if ($signos->observaciones)
                    <p class="mt-4 text-sm leading-6 text-on-surface-variant">{{ $signos->observaciones }}</p>
                @endif
            @empty
                <p class="mt-2 text-sm text-on-surface-variant">No se registraron signos vitales.</p>
            @endforelse
        </article>

        <article class="rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:p-6">
            <h2 class="text-lg font-bold text-primary">Vacunas aplicadas</h2>
            @forelse ($atencion->vacunaciones as $vacunacion)
                <div class="mt-3 flex items-start justify-between gap-3 rounded-xl bg-surface-container-low p-3">
                    <div>
                        <p class="font-semibold text-on-surface">{{ $vacunacion->vacuna->nombre }}</p>
                        <p class="mt-1 text-xs text-on-surface-variant">Aplicada el {{ $vacunacion->fecha_aplicacion->format('d/m/Y') }}</p>
                    </div>
                    <span class="rounded-full bg-secondary-container px-3 py-1 text-xs font-semibold text-on-secondary-container">Aplicada</span>
                </div>
            @empty
                <p class="mt-2 text-sm text-on-surface-variant">No se registraron vacunas aplicadas en esta consulta.</p>
            @endforelse
        </article>
    </section>

    <section class="mt-6 rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-primary">Notas clínicas</h2>
                <p class="mt-1 text-sm text-on-surface-variant">Las notas se conservan en versiones consecutivas; no se pueden editar ni eliminar.</p>
            </div>
            <span class="rounded-full bg-surface-container px-3 py-1 text-xs font-semibold text-on-surface-variant">{{ $atencion->notas->count() }} {{ $atencion->notas->count() === 1 ? 'versión' : 'versiones' }}</span>
        </div>
        <div class="mt-5 flex flex-col gap-4">
            @foreach ($atencion->notas as $nota)
                <article class="rounded-xl border border-outline-variant/50 p-4">
                    <div class="flex flex-wrap justify-between gap-2">
                        <h3 class="font-semibold text-primary">Versión {{ $nota->version }}</h3>
                        <p class="text-xs text-on-surface-variant">{{ $nota->created_at->format('d/m/Y H:i') }} · {{ $nota->autor?->name ?? 'Usuario sin cuenta' }}</p>
                    </div>
                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-on-surface">{{ $nota->contenido }}</p>
                </article>
            @endforeach
        </div>
        <form method="POST" action="{{ route('portal.atenciones.notas.store', $atencion) }}" class="mt-6 flex flex-col gap-4 border-t border-outline-variant/40 pt-5">
            @csrf
            <div class="flex flex-col gap-2">
                <label for="contenido" class="text-sm font-semibold text-primary">Agregar nota de seguimiento</label>
                <textarea id="contenido" name="contenido" rows="4" maxlength="10000" required class="rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">{{ old('contenido') }}</textarea>
                @error('contenido') <p class="text-sm text-error">{{ $message }}</p> @enderror
            </div>
            <label class="flex min-h-12 items-start gap-3 rounded-lg bg-error-container/50 px-4 py-3 text-sm font-semibold text-on-error-container">
                <input type="hidden" name="hospitalizacion_recomendada" value="0">
                <input type="checkbox" name="hospitalizacion_recomendada" value="1" @checked(old('hospitalizacion_recomendada', $atencion->hospitalizacion_recomendada)) class="mt-0.5 size-4 accent-error">
                Mantener o activar recomendación de hospitalización
            </label>
            <button type="submit" class="min-h-11 w-fit rounded-lg bg-primary-container px-5 py-2 text-sm font-semibold text-on-primary transition-colors hover:bg-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                Agregar nueva versión
            </button>
        </form>
    </section>

    <section class="mt-6 rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:p-6">
        <h2 class="text-lg font-bold text-primary">Historial clínico de {{ $atencion->mascota->nombre }}</h2>
        @forelse ($historial as $registro)
            <article class="mt-3 rounded-xl bg-surface-container-low p-4">
                <div class="flex flex-wrap justify-between gap-2">
                    <p class="font-semibold text-on-surface">{{ $registro->fecha_atencion->format('d/m/Y H:i') }} · {{ $registro->folio }}</p>
                    @if ($registro->hospitalizacion_recomendada)
                        <span class="text-xs font-semibold text-error">Hospitalización recomendada</span>
                    @endif
                </div>
                @if ($registro->notas->first())
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-on-surface-variant">{{ $registro->notas->first()->contenido }}</p>
                @endif
            </article>
        @empty
            <p class="mt-2 text-sm text-on-surface-variant">Este es el primer registro del historial clínico.</p>
        @endforelse
    </section>
@endsection
