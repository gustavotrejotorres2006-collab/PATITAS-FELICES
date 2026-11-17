<div class="grid gap-5 sm:grid-cols-2">
    <div class="flex flex-col gap-2">
        <label for="nombre" class="text-sm font-semibold text-primary">Nombre <span aria-hidden="true">*</span></label>
        <input id="nombre" name="nombre" value="{{ old('nombre', $propietario->nombre ?? '') }}" maxlength="255" required autocomplete="given-name" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('nombre') aria-invalid="true" aria-describedby="nombre-error" @enderror>
        @error('nombre') <p id="nombre-error" class="text-sm text-error">{{ $message }}</p> @enderror
    </div>
    <div class="flex flex-col gap-2">
        <label for="apellido" class="text-sm font-semibold text-primary">Apellidos</label>
        <input id="apellido" name="apellido" value="{{ old('apellido', $propietario->apellido ?? '') }}" maxlength="255" autocomplete="family-name" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('apellido') aria-invalid="true" aria-describedby="apellido-error" @enderror>
        @error('apellido') <p id="apellido-error" class="text-sm text-error">{{ $message }}</p> @enderror
    </div>
    <div class="flex flex-col gap-2">
        <label for="telefono" class="text-sm font-semibold text-primary">Teléfono</label>
        <input id="telefono" name="telefono" type="tel" value="{{ old('telefono', $propietario->telefono ?? '') }}" maxlength="50" autocomplete="tel" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('telefono') aria-invalid="true" aria-describedby="telefono-error" @enderror>
        @error('telefono') <p id="telefono-error" class="text-sm text-error">{{ $message }}</p> @enderror
    </div>
    <div class="flex flex-col gap-2">
        <label for="email" class="text-sm font-semibold text-primary">Correo electrónico</label>
        <input id="email" name="email" type="email" value="{{ old('email', $propietario->email ?? '') }}" maxlength="255" autocomplete="email" class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
        @error('email') <p id="email-error" class="text-sm text-error">{{ $message }}</p> @enderror
    </div>
    <div class="flex flex-col gap-2 sm:col-span-2">
        <label for="direccion" class="text-sm font-semibold text-primary">Dirección</label>
        <textarea id="direccion" name="direccion" rows="3" maxlength="2000" autocomplete="street-address" class="rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('direccion') aria-invalid="true" aria-describedby="direccion-error" @enderror>{{ old('direccion', $propietario->direccion ?? '') }}</textarea>
        @error('direccion') <p id="direccion-error" class="text-sm text-error">{{ $message }}</p> @enderror
    </div>
</div>
