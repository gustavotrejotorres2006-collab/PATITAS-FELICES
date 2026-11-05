<div class="grid gap-5 sm:grid-cols-2">
    <div class="flex flex-col gap-2">
        <label for="nombre" class="text-sm font-semibold text-primary">Nombre <span aria-hidden="true">*</span></label>
        <input id="nombre" name="nombre" value="{{ old('nombre', $mascota->nombre ?? '') }}" maxlength="255" required class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('nombre') aria-invalid="true" aria-describedby="nombre-error" @enderror>
        @error('nombre') <p id="nombre-error" class="text-sm text-error">{{ $message }}</p> @enderror
    </div>
    <div class="flex flex-col gap-2">
        <label for="especie" class="text-sm font-semibold text-primary">Especie <span aria-hidden="true">*</span></label>
        <select id="especie" name="especie" required class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('especie') aria-invalid="true" aria-describedby="especie-error" @enderror>
            <option value="">Selecciona una especie</option>
            @foreach (['perro' => 'Perro', 'gato' => 'Gato', 'ave' => 'Ave', 'conejo' => 'Conejo', 'reptil' => 'Reptil', 'otro' => 'Otro'] as $value => $label)
                <option value="{{ $value }}" @selected(old('especie', $mascota->especie ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('especie') <p id="especie-error" class="text-sm text-error">{{ $message }}</p> @enderror
    </div>
    <div class="flex flex-col gap-2">
        <label for="raza" class="text-sm font-semibold text-primary">Raza</label>
        <input id="raza" name="raza" value="{{ old('raza', $mascota->raza ?? '') }}" maxlength="255" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('raza') aria-invalid="true" aria-describedby="raza-error" @enderror>
        @error('raza') <p id="raza-error" class="text-sm text-error">{{ $message }}</p> @enderror
    </div>
    <div class="flex flex-col gap-2">
        <label for="sexo" class="text-sm font-semibold text-primary">Sexo</label>
        <select id="sexo" name="sexo" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('sexo') aria-invalid="true" aria-describedby="sexo-error" @enderror>
            <option value="">Sin especificar</option>
            <option value="macho" @selected(old('sexo', $mascota->sexo ?? '') === 'macho')>Macho</option>
            <option value="hembra" @selected(old('sexo', $mascota->sexo ?? '') === 'hembra')>Hembra</option>
            <option value="no_especificado" @selected(old('sexo', $mascota->sexo ?? '') === 'no_especificado')>Prefiero no especificar</option>
        </select>
        @error('sexo') <p id="sexo-error" class="text-sm text-error">{{ $message }}</p> @enderror
    </div>
    <div class="flex flex-col gap-2">
        <label for="fecha_nacimiento" class="text-sm font-semibold text-primary">Fecha de nacimiento</label>
        <input id="fecha_nacimiento" name="fecha_nacimiento" type="date" value="{{ old('fecha_nacimiento', isset($mascota) ? $mascota->fecha_nacimiento?->format('Y-m-d') : '') }}" max="{{ now()->toDateString() }}" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('fecha_nacimiento') aria-invalid="true" aria-describedby="fecha-nacimiento-error" @enderror>
        @error('fecha_nacimiento') <p id="fecha-nacimiento-error" class="text-sm text-error">{{ $message }}</p> @enderror
    </div>
    <div class="flex flex-col gap-2">
        <label for="color" class="text-sm font-semibold text-primary">Color</label>
        <input id="color" name="color" value="{{ old('color', $mascota->color ?? '') }}" maxlength="255" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('color') aria-invalid="true" aria-describedby="color-error" @enderror>
        @error('color') <p id="color-error" class="text-sm text-error">{{ $message }}</p> @enderror
    </div>
    <div class="flex flex-col gap-2 sm:col-span-2">
        <label for="microchip" class="text-sm font-semibold text-primary">Número de microchip</label>
        <input id="microchip" name="microchip" value="{{ old('microchip', $mascota->microchip ?? '') }}" maxlength="255" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('microchip') aria-invalid="true" aria-describedby="microchip-error" @enderror>
        @error('microchip') <p id="microchip-error" class="text-sm text-error">{{ $message }}</p> @enderror
    </div>
    <div class="flex flex-col gap-2 sm:col-span-2">
        <label for="observaciones" class="text-sm font-semibold text-primary">Observaciones</label>
        <textarea id="observaciones" name="observaciones" rows="4" maxlength="5000" class="rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('observaciones') aria-invalid="true" aria-describedby="observaciones-error" @enderror>{{ old('observaciones', $mascota->observaciones ?? '') }}</textarea>
        @error('observaciones') <p id="observaciones-error" class="text-sm text-error">{{ $message }}</p> @enderror
    </div>
    <label for="esterilizado" class="flex min-h-12 items-center gap-3 rounded-lg bg-surface-container-low px-4 py-3 text-sm font-semibold text-primary sm:col-span-2">
        <input id="esterilizado" name="esterilizado" type="checkbox" value="1" @checked(old('esterilizado', $mascota->esterilizado ?? false)) class="size-5 rounded border-outline-variant text-primary focus:ring-secondary">
        Esterilizado/a
    </label>
</div>
