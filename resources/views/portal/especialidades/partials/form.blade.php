<div class="flex flex-col gap-5">
    <div class="flex flex-col gap-2">
        <label for="nombre" class="text-sm font-semibold text-primary">Nombre <span aria-hidden="true">*</span></label>
        <input id="nombre" name="nombre" value="{{ old('nombre', $especialidad->nombre ?? '') }}" maxlength="255" required class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30" @error('nombre') aria-invalid="true" aria-describedby="nombre-error" @enderror>
        @error('nombre') <p id="nombre-error" class="text-sm text-error">{{ $message }}</p> @enderror
    </div>
    <div class="flex flex-col gap-2">
        <label for="descripcion" class="text-sm font-semibold text-primary">Descripción</label>
        <textarea id="descripcion" name="descripcion" rows="4" maxlength="2000" class="rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">{{ old('descripcion', $especialidad->descripcion ?? '') }}</textarea>
        @error('descripcion') <p class="text-sm text-error">{{ $message }}</p> @enderror
    </div>
    @if (isset($especialidad))
        <label class="flex min-h-11 items-center gap-3 text-sm font-semibold text-primary">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" name="activo" value="1" @checked((bool) old('activo', $especialidad->activo)) class="size-4 accent-primary">
            Especialidad activa
        </label>
    @endif
</div>
