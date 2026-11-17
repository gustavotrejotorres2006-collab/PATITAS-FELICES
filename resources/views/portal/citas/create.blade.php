@extends('layouts.portal')

@section('title', 'Nueva cita')
@section('portal-section', 'Atención médica')
@section('page-heading', 'Programar una cita')
@section('meta-description', 'Registra una cita en la agenda veterinaria.')

@section('breadcrumbs')
    <nav aria-label="Ruta de navegación" class="mb-6 flex items-center gap-2 text-sm text-on-surface-variant">
        <a href="{{ route('portal.citas.index') }}" class="font-semibold text-primary underline">Agenda de citas</a>
        <span aria-hidden="true">/</span>
        <span aria-current="page">Nueva cita</span>
    </nav>
@endsection

@section('content')
    <form method="POST" action="{{ route('portal.citas.store') }}" class="max-w-4xl rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:p-8">
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
            <label class="flex min-h-12 items-center gap-3 rounded-lg bg-error-container/50 px-4 py-3 text-sm font-semibold text-on-error-container sm:col-span-2">
                <input type="checkbox" name="urgente" value="1" @checked(old('urgente')) class="size-4 accent-error">
                Urgencia (prioridad máxima)
            </label>

            <div class="flex flex-col gap-2">
                <label for="mascota_id" class="text-sm font-semibold text-primary">Mascota <span aria-hidden="true">*</span></label>
                <select id="mascota_id" name="mascota_id" required class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">
                    <option value="">Selecciona una mascota</option>
                    @foreach ($mascotas as $mascota)
                        <option value="{{ $mascota->id }}" @selected((string) old('mascota_id') === (string) $mascota->id)>{{ $mascota->nombre }} · {{ ucfirst($mascota->especie) }}</option>
                    @endforeach
                </select>
                @error('mascota_id') <p class="text-sm text-error">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="propietario_id" class="text-sm font-semibold text-primary">Propietario <span aria-hidden="true">*</span></label>
                <select id="propietario_id" name="propietario_id" required class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">
                    <option value="">Selecciona un propietario</option>
                    @foreach ($propietarios as $propietario)
                        <option value="{{ $propietario->id }}" @selected((string) old('propietario_id') === (string) $propietario->id)>{{ $propietario->nombre }}</option>
                    @endforeach
                </select>
                @error('propietario_id') <p class="text-sm text-error">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="profesional_id" class="text-sm font-semibold text-primary">Profesional <span aria-hidden="true">*</span></label>
                <select id="profesional_id" name="profesional_id" required class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">
                    <option value="">Selecciona un profesional</option>
                    @foreach ($profesionales as $profesional)
                        <option value="{{ $profesional->id }}" @selected((string) old('profesional_id') === (string) $profesional->id)>{{ trim($profesional->nombre.' '.$profesional->apellido) }}</option>
                    @endforeach
                </select>
                @error('profesional_id') <p class="text-sm text-error">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="inicio" class="text-sm font-semibold text-primary">Fecha y hora de inicio <span aria-hidden="true">*</span></label>
                <input id="inicio" name="inicio" type="datetime-local" value="{{ old('inicio') }}" min="{{ now()->format('Y-m-d\TH:i') }}" required class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">
                @error('inicio') <p class="text-sm text-error">{{ $message }}</p> @enderror
            </div>

            <fieldset class="flex flex-col gap-2 sm:col-span-2">
                <legend class="text-sm font-semibold text-primary">Servicios <span aria-hidden="true">*</span></legend>
                <p class="text-xs leading-5 text-on-surface-variant">El horario se calcula con la duración de los servicios. Cirugía requiere 90 minutos de separación; los demás servicios, 30 minutos.</p>
                <div class="grid gap-2 sm:grid-cols-2">
                    @php
                        $serviciosSeleccionados = old('servicios', []);
                        $serviciosSeleccionados = is_array($serviciosSeleccionados)
                            ? array_map('strval', $serviciosSeleccionados)
                            : [];
                    @endphp
                    @forelse ($servicios as $servicio)
                        <label class="flex min-h-12 items-center gap-3 rounded-lg border border-outline-variant/50 px-3 py-2 text-sm text-on-surface hover:bg-surface-container-low">
                            <input type="checkbox" name="servicios[]" value="{{ $servicio->id }}" @checked(in_array((string) $servicio->id, $serviciosSeleccionados, true)) class="size-4 accent-primary">
                            <span class="min-w-0 flex-1">
                                <span class="block font-semibold">{{ $servicio->nombre }}</span>
                                <span class="block text-xs text-on-surface-variant">{{ $servicio->categoria?->nombre ?? 'Sin categoría' }} · {{ $servicio->duracion_minutos }} min · ${{ number_format((float) $servicio->precio, 2) }}</span>
                            </span>
                        </label>
                    @empty
                        <p class="rounded-lg bg-surface-container-low px-4 py-3 text-sm text-on-surface-variant">No hay servicios activos disponibles.</p>
                    @endforelse
                </div>
                @error('servicios') <p class="text-sm text-error">{{ $message }}</p> @enderror
                @error('servicios.*') <p class="text-sm text-error">{{ $message }}</p> @enderror
            </fieldset>

            <div class="flex flex-col gap-2 sm:col-span-2">
                <label for="motivo" class="text-sm font-semibold text-primary">Motivo u observaciones</label>
                <textarea id="motivo" name="motivo" rows="3" maxlength="2000" class="rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">{{ old('motivo') }}</textarea>
                @error('motivo') <p class="text-sm text-error">{{ $message }}</p> @enderror
            </div>

        </div>

        <div class="mt-8 flex flex-wrap gap-3 border-t border-outline-variant/40 pt-5">
            <button type="submit" class="min-h-11 rounded-lg bg-primary-container px-5 py-2 text-sm font-semibold text-on-primary transition-colors hover:bg-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                Agregar a la agenda
            </button>
            <a href="{{ route('portal.citas.index') }}" class="min-h-11 content-center rounded-lg px-5 py-2 text-sm font-semibold text-on-surface-variant transition-colors hover:bg-surface-container-low">
                Cancelar
            </a>
        </div>
    </form>
@endsection
