<div class="grid gap-5 sm:grid-cols-2">
    <div class="flex flex-col gap-2">
        <label for="nombre" class="text-sm font-semibold text-primary">Nombre <span aria-hidden="true">*</span></label>
        <input id="nombre" name="nombre" value="{{ old('nombre', $profesional->nombre ?? '') }}" maxlength="255" required autocomplete="given-name" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('nombre') aria-invalid="true" aria-describedby="nombre-error" @enderror>
        @error('nombre') <p id="nombre-error" class="text-sm text-error">{{ $message }}</p> @enderror
    </div>
    <div class="flex flex-col gap-2">
        <label for="apellido" class="text-sm font-semibold text-primary">Apellidos</label>
        <input id="apellido" name="apellido" value="{{ old('apellido', $profesional->apellido ?? '') }}" maxlength="255" autocomplete="family-name" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">
        @error('apellido') <p class="text-sm text-error">{{ $message }}</p> @enderror
    </div>
    <div class="flex flex-col gap-2">
        <label for="cedula_profesional" class="text-sm font-semibold text-primary">Cédula profesional</label>
        <input id="cedula_profesional" name="cedula_profesional" value="{{ old('cedula_profesional', $profesional->cedula_profesional ?? '') }}" maxlength="100" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('cedula_profesional') aria-invalid="true" aria-describedby="cedula-error" @enderror>
        @error('cedula_profesional') <p id="cedula-error" class="text-sm text-error">{{ $message }}</p> @enderror
    </div>
    <div class="flex flex-col gap-2">
        <label for="telefono" class="text-sm font-semibold text-primary">Teléfono</label>
        <input id="telefono" name="telefono" type="tel" value="{{ old('telefono', $profesional->telefono ?? '') }}" maxlength="50" autocomplete="tel" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">
        @error('telefono') <p class="text-sm text-error">{{ $message }}</p> @enderror
    </div>
    <div class="flex flex-col gap-2">
        <label for="estado" class="text-sm font-semibold text-primary">Estado</label>
        <select id="estado" name="estado" required class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">
            <option value="activo" @selected(old('estado', $profesional->estado ?? 'activo') === 'activo')>Activo</option>
            <option value="inactivo" @selected(old('estado', $profesional->estado ?? 'activo') === 'inactivo')>Inactivo</option>
        </select>
        @error('estado') <p class="text-sm text-error">{{ $message }}</p> @enderror
    </div>
    <fieldset class="flex flex-col gap-2 sm:col-span-2">
        <legend class="text-sm font-semibold text-primary">Especialidades</legend>
        @php
            $seleccionadas = old('especialidades', isset($profesional) ? $profesional->especialidades->pluck('id')->all() : []);
            $seleccionadas = array_map('strval', $seleccionadas);
        @endphp
        @forelse ($especialidades as $especialidad)
            <label class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm text-on-surface hover:bg-surface-container-low">
                <input type="checkbox" name="especialidades[]" value="{{ $especialidad->id }}" @checked(in_array((string) $especialidad->id, $seleccionadas, true)) class="size-4 accent-primary">
                <span>{{ $especialidad->nombre }}{{ $especialidad->activo ? '' : ' (inactiva)' }}</span>
            </label>
        @empty
            <p class="rounded-lg bg-surface-container-low px-4 py-3 text-sm text-on-surface-variant">
                Aún no hay especialidades.
                <a href="{{ route('portal.especialidades.create') }}" class="font-semibold text-primary underline">Registra la primera</a>.
            </p>
        @endforelse
        @error('especialidades.*') <p class="text-sm text-error">{{ $message }}</p> @enderror
    </fieldset>
</div>
