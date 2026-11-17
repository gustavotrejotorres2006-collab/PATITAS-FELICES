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
    <fieldset class="flex flex-col gap-4 rounded-xl border border-outline-variant/50 p-4 sm:col-span-2">
        <legend class="px-1 text-sm font-semibold text-primary">Cuenta de acceso clínico</legend>
        <p class="text-sm leading-6 text-on-surface-variant">
            Vincula una cuenta para que el profesional pueda iniciar sesión y consultar sus expedientes. El acceso tendrá el rol Veterinario.
            @if (isset($profesional) && $profesional->user)
                Deja la contraseña vacía para conservar la actual.
            @else
                La contraseña debe tener al menos 8 caracteres.
            @endif
        </p>
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="flex flex-col gap-2">
                <label for="usuario_email" class="text-sm font-semibold text-primary">Correo de acceso</label>
                <input id="usuario_email" name="usuario_email" type="email" value="{{ old('usuario_email', $profesional->user?->email ?? '') }}" maxlength="255" autocomplete="email" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('usuario_email') aria-invalid="true" aria-describedby="usuario-email-error" @enderror>
                @error('usuario_email') <p id="usuario-email-error" class="text-sm text-error">{{ $message }}</p> @enderror
            </div>
            <div class="flex flex-col gap-2">
                <label for="usuario_password" class="text-sm font-semibold text-primary">{{ isset($profesional) && $profesional->user ? 'Nueva contraseña (opcional)' : 'Contraseña inicial' }}</label>
                <input id="usuario_password" name="usuario_password" type="password" minlength="8" autocomplete="new-password" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('usuario_password') aria-invalid="true" aria-describedby="usuario-password-error" @enderror>
                @error('usuario_password') <p id="usuario-password-error" class="text-sm text-error">{{ $message }}</p> @enderror
            </div>
        </div>
    </fieldset>
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
