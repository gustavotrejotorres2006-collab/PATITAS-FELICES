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
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="flex flex-col gap-2">
                    <label for="nombre" class="text-sm font-semibold text-primary">Nombre <span aria-hidden="true">*</span></label>
                    <input id="nombre" name="nombre" value="{{ old('nombre') }}" maxlength="255" required class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('nombre') aria-invalid="true" aria-describedby="nombre-error" @enderror>
                    @error('nombre') <p id="nombre-error" class="text-sm text-error">{{ $message }}</p> @enderror
                </div>
                <div class="flex flex-col gap-2">
                    <label for="especie" class="text-sm font-semibold text-primary">Especie <span aria-hidden="true">*</span></label>
                    <select id="especie" name="especie" required class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('especie') aria-invalid="true" aria-describedby="especie-error" @enderror>
                        <option value="">Selecciona una especie</option>
                        @foreach (['perro' => 'Perro', 'gato' => 'Gato', 'ave' => 'Ave', 'conejo' => 'Conejo', 'reptil' => 'Reptil', 'otro' => 'Otro'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('especie') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('especie') <p id="especie-error" class="text-sm text-error">{{ $message }}</p> @enderror
                </div>
                <div class="flex flex-col gap-2">
                    <label for="raza" class="text-sm font-semibold text-primary">Raza</label>
                    <input id="raza" name="raza" value="{{ old('raza') }}" maxlength="255" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('raza') aria-invalid="true" aria-describedby="raza-error" @enderror>
                    @error('raza') <p id="raza-error" class="text-sm text-error">{{ $message }}</p> @enderror
                </div>
                <div class="flex flex-col gap-2">
                    <label for="sexo" class="text-sm font-semibold text-primary">Sexo</label>
                    <select id="sexo" name="sexo" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('sexo') aria-invalid="true" aria-describedby="sexo-error" @enderror>
                        <option value="">Sin especificar</option>
                        <option value="macho" @selected(old('sexo') === 'macho')>Macho</option>
                        <option value="hembra" @selected(old('sexo') === 'hembra')>Hembra</option>
                        <option value="no_especificado" @selected(old('sexo') === 'no_especificado')>Prefiero no especificar</option>
                    </select>
                    @error('sexo') <p id="sexo-error" class="text-sm text-error">{{ $message }}</p> @enderror
                </div>
                <div class="flex flex-col gap-2">
                    <label for="fecha_nacimiento" class="text-sm font-semibold text-primary">Fecha de nacimiento</label>
                    <input id="fecha_nacimiento" name="fecha_nacimiento" type="date" value="{{ old('fecha_nacimiento') }}" max="{{ now()->toDateString() }}" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('fecha_nacimiento') aria-invalid="true" aria-describedby="fecha-nacimiento-error" @enderror>
                    @error('fecha_nacimiento') <p id="fecha-nacimiento-error" class="text-sm text-error">{{ $message }}</p> @enderror
                </div>
                <div class="flex flex-col gap-2">
                    <label for="color" class="text-sm font-semibold text-primary">Color</label>
                    <input id="color" name="color" value="{{ old('color') }}" maxlength="255" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('color') aria-invalid="true" aria-describedby="color-error" @enderror>
                    @error('color') <p id="color-error" class="text-sm text-error">{{ $message }}</p> @enderror
                </div>
                <div class="flex flex-col gap-2 sm:col-span-2">
                    <label for="microchip" class="text-sm font-semibold text-primary">Número de microchip</label>
                    <input id="microchip" name="microchip" value="{{ old('microchip') }}" maxlength="255" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('microchip') aria-invalid="true" aria-describedby="microchip-error" @enderror>
                    @error('microchip') <p id="microchip-error" class="text-sm text-error">{{ $message }}</p> @enderror
                </div>
                <div class="flex flex-col gap-2 sm:col-span-2">
                    <label for="observaciones" class="text-sm font-semibold text-primary">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="4" maxlength="5000" class="rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('observaciones') aria-invalid="true" aria-describedby="observaciones-error" @enderror>{{ old('observaciones') }}</textarea>
                    @error('observaciones') <p id="observaciones-error" class="text-sm text-error">{{ $message }}</p> @enderror
                </div>
                <label for="esterilizado" class="flex min-h-12 items-center gap-3 rounded-lg bg-surface-container-low px-4 py-3 text-sm font-semibold text-primary sm:col-span-2">
                    <input id="esterilizado" name="esterilizado" type="checkbox" value="1" @checked(old('esterilizado')) class="size-5 rounded border-outline-variant text-primary focus:ring-secondary">
                    Esterilizado/a
                </label>
            </div>

            <div class="flex flex-wrap justify-end gap-3 border-t border-outline-variant/40 pt-5">
                <a href="{{ route('portal.propietarios.show', $propietario) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-outline-variant px-4 py-2 font-semibold text-primary hover:bg-surface-container-low">Cancelar</a>
                <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-primary-container px-5 py-2 font-semibold text-on-primary hover:bg-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                    Guardar mascota
                </button>
            </div>
        </form>
    </section>
@endsection
