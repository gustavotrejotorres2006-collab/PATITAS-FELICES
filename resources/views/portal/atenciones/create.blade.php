@extends('layouts.portal')

@section('title', 'Registrar atención')
@section('portal-section', 'Módulo médico')
@section('page-heading', 'Expediente clínico')
@section('meta-description', 'Registra el diagnóstico, signos vitales y nota clínica de la consulta.')

@section('breadcrumbs')
    <nav aria-label="Ruta de navegación" class="mb-6 flex items-center gap-2 text-sm text-on-surface-variant">
        <a href="{{ route('portal.atenciones.index') }}" class="font-semibold text-primary underline">Atención clínica</a>
        <span aria-hidden="true">/</span>
        <span aria-current="page">Registrar atención</span>
    </nav>
@endsection

@section('content')
    <section class="mb-6 rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:p-6">
        <p class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Cita {{ $cita->folio }}</p>
        <h2 class="mt-2 text-xl font-bold text-primary">{{ $cita->mascota->nombre }}</h2>
        <p class="mt-1 text-sm text-on-surface-variant">
            {{ ucfirst($cita->mascota->especie) }} · {{ $cita->propietario?->nombre ?? 'Sin propietario' }} · {{ $cita->inicio->format('d/m/Y H:i') }}
        </p>
        @if ($cita->motivo)
            <p class="mt-3 text-sm text-on-surface"><span class="font-semibold">Motivo de la cita:</span> {{ $cita->motivo }}</p>
        @endif
    </section>

    <form method="POST" action="{{ route('portal.atenciones.store', $cita) }}" class="max-w-4xl rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:p-8">
        @csrf

        @if ($errors->any())
            <div role="alert" class="mb-6 rounded-xl border border-error/30 bg-error-container px-4 py-3 text-sm text-on-error-container">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="flex flex-col gap-2">
                <label for="fecha_atencion" class="text-sm font-semibold text-primary">Fecha y hora de atención <span aria-hidden="true">*</span></label>
                <input id="fecha_atencion" name="fecha_atencion" type="datetime-local" value="{{ old('fecha_atencion', now()->format('Y-m-d\TH:i')) }}" max="{{ now()->format('Y-m-d\TH:i') }}" required class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">
                @error('fecha_atencion') <p class="text-sm text-error">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="motivo" class="text-sm font-semibold text-primary">Motivo de consulta</label>
                <input id="motivo" name="motivo" value="{{ old('motivo', $cita->motivo) }}" maxlength="2000" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">
                @error('motivo') <p class="text-sm text-error">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-2 sm:col-span-2">
                <label for="diagnostico" class="text-sm font-semibold text-primary">Diagnóstico <span aria-hidden="true">*</span></label>
                <textarea id="diagnostico" name="diagnostico" rows="3" maxlength="1000" required class="rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">{{ old('diagnostico') }}</textarea>
                @error('diagnostico') <p class="text-sm text-error">{{ $message }}</p> @enderror
            </div>

            <fieldset class="flex flex-col gap-3 sm:col-span-2">
                <legend class="text-sm font-semibold text-primary">Signos vitales</legend>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="flex flex-col gap-2">
                        <label for="peso_kg" class="text-sm text-on-surface-variant">Peso (kg)</label>
                        <input id="peso_kg" name="peso_kg" type="number" step="0.01" min="0.01" max="1000" value="{{ old('peso_kg') }}" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">
                        @error('peso_kg') <p class="text-sm text-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="temperatura_c" class="text-sm text-on-surface-variant">Temperatura (°C)</label>
                        <input id="temperatura_c" name="temperatura_c" type="number" step="0.01" min="20" max="50" value="{{ old('temperatura_c') }}" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">
                        @error('temperatura_c') <p class="text-sm text-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="frecuencia_cardiaca" class="text-sm text-on-surface-variant">Frecuencia cardiaca (lpm)</label>
                        <input id="frecuencia_cardiaca" name="frecuencia_cardiaca" type="number" min="1" max="500" value="{{ old('frecuencia_cardiaca') }}" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">
                        @error('frecuencia_cardiaca') <p class="text-sm text-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="frecuencia_respiratoria" class="text-sm text-on-surface-variant">Frecuencia respiratoria (rpm)</label>
                        <input id="frecuencia_respiratoria" name="frecuencia_respiratoria" type="number" min="1" max="300" value="{{ old('frecuencia_respiratoria') }}" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">
                        @error('frecuencia_respiratoria') <p class="text-sm text-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="presion_arterial" class="text-sm text-on-surface-variant">Presión arterial</label>
                        <input id="presion_arterial" name="presion_arterial" value="{{ old('presion_arterial') }}" maxlength="50" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">
                        @error('presion_arterial') <p class="text-sm text-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="condicion_corporal" class="text-sm text-on-surface-variant">Condición corporal (1–9)</label>
                        <input id="condicion_corporal" name="condicion_corporal" type="number" step="0.1" min="1" max="9" value="{{ old('condicion_corporal') }}" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">
                        @error('condicion_corporal') <p class="text-sm text-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="observaciones_vitales" class="text-sm text-on-surface-variant">Observaciones de signos vitales</label>
                    <textarea id="observaciones_vitales" name="observaciones_vitales" rows="2" maxlength="2000" class="rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">{{ old('observaciones_vitales') }}</textarea>
                </div>
            </fieldset>

            <fieldset class="flex flex-col gap-2 sm:col-span-2">
                <legend class="text-sm font-semibold text-primary">Vacunas aplicadas en esta consulta</legend>
                <p class="text-xs leading-5 text-on-surface-variant">Solo las vacunas que marques como aplicadas se agregarán al historial de la mascota. No se registran vacunas programadas desde esta pantalla.</p>
                <div class="grid gap-2 sm:grid-cols-2">
                    @forelse ($vacunas as $vacuna)
                        <label class="flex min-h-12 items-center gap-3 rounded-lg border border-outline-variant/50 px-3 py-2 text-sm text-on-surface hover:bg-surface-container-low">
                            <input type="checkbox" name="vacunas_aplicadas[]" value="{{ $vacuna->id }}" @checked(in_array((string) $vacuna->id, array_map('strval', old('vacunas_aplicadas', [])), true)) class="size-4 accent-primary">
                            <span>
                                <span class="block font-semibold">{{ $vacuna->nombre }}</span>
                                <span class="block text-xs text-on-surface-variant">{{ $vacuna->especie ?: 'Todas las especies' }} · Registrar como aplicada hoy</span>
                            </span>
                        </label>
                    @empty
                        <p class="rounded-lg bg-surface-container-low px-4 py-3 text-sm text-on-surface-variant">No hay vacunas activas en el catálogo.</p>
                    @endforelse
                </div>
                @error('vacunas_aplicadas') <p class="text-sm text-error">{{ $message }}</p> @enderror
                @error('vacunas_aplicadas.*') <p class="text-sm text-error">{{ $message }}</p> @enderror
            </fieldset>

            <div class="flex flex-col gap-2 sm:col-span-2">
                <label for="nota" class="text-sm font-semibold text-primary">Nota clínica <span aria-hidden="true">*</span></label>
                <textarea id="nota" name="nota" rows="5" maxlength="10000" required class="rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">{{ old('nota') }}</textarea>
                @error('nota') <p class="text-sm text-error">{{ $message }}</p> @enderror
            </div>

            <label class="flex min-h-12 items-start gap-3 rounded-lg bg-error-container/50 px-4 py-3 text-sm font-semibold text-on-error-container sm:col-span-2">
                <input type="hidden" name="hospitalizacion_recomendada" value="0">
                <input type="checkbox" name="hospitalizacion_recomendada" value="1" @checked(old('hospitalizacion_recomendada')) class="mt-0.5 size-4 accent-error">
                Recomendar hospitalización para esta mascota
            </label>
        </div>

        <div class="mt-8 flex flex-wrap gap-3 border-t border-outline-variant/40 pt-5">
            <button type="submit" class="min-h-11 rounded-lg bg-primary-container px-5 py-2 text-sm font-semibold text-on-primary transition-colors hover:bg-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                Guardar expediente
            </button>
            <a href="{{ route('portal.atenciones.index') }}" class="min-h-11 content-center rounded-lg px-5 py-2 text-sm font-semibold text-on-surface-variant transition-colors hover:bg-surface-container-low">
                Cancelar
            </a>
        </div>
    </form>
@endsection
