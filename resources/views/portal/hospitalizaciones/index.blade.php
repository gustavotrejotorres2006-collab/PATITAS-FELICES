@extends('layouts.portal')

@section('title', 'Quirófano y hospitalización')
@section('portal-section', 'Atención clínica')
@section('page-heading', 'Quirófano y hospitalización')
@section('meta-description', 'Programa cirugías, administra hospitalizaciones y controla monitoreos clínicos.')

@section('breadcrumbs')
    <nav aria-label="Ruta de navegación" class="mb-6 text-sm text-on-surface-variant">
        <span aria-current="page" class="font-semibold text-primary">Quirófano y hospitalización</span>
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

    @if ($hospitalizaciones->isNotEmpty())
        <section
            id="monitoring-alert"
            role="alert"
            aria-live="assertive"
            data-monitoring-alert
            @if ($hospitalizacionesVencidas->isEmpty()) style="display: none;" @endif
            class="mb-6 flex flex-col gap-4 rounded-2xl border border-error/40 bg-error-container p-5 text-on-error-container sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="font-bold">Monitoreo hospitalario vencido</h2>
                <p class="mt-1 text-sm leading-6">
                    Hay <span id="monitoring-alert-count">{{ $hospitalizacionesVencidas->count() }}</span> paciente(s) que requieren monitoreo. Regístralo cuanto antes.
                </p>
            </div>
            <button id="monitoring-alarm" type="button" class="min-h-11 shrink-0 rounded-lg bg-error px-4 py-2 text-sm font-bold text-on-error focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                Activar alarma sonora
            </button>
        </section>
    @endif

    <div class="grid gap-6 xl:grid-cols-2">
        <section aria-labelledby="schedule-surgery-heading" class="rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:p-6">
            <h2 id="schedule-surgery-heading" class="text-lg font-bold text-primary">Programar cirugía</h2>
            <p class="mt-1 text-sm leading-6 text-on-surface-variant">
                Requiere atención y nota clínica previas, consentimiento físico firmado y disponibilidad del quirófano y ambos profesionales.
            </p>
            <form method="POST" action="{{ route('portal.cirugias.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                @csrf
                <div class="flex flex-col gap-2 sm:col-span-2">
                    <label for="surgery-atencion" class="text-sm font-semibold text-primary">Paciente y atención</label>
                    <select id="surgery-atencion" name="atencion_id" required class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                        <option value="">Selecciona una atención clínica</option>
                        @foreach ($surgeryAttentions as $attention)
                            <option value="{{ $attention->id }}" @selected(old('atencion_id') == $attention->id)>
                                {{ $attention->mascota->nombre }} · {{ $attention->propietario->nombre }} · {{ $attention->folio }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="surgery-space" class="text-sm font-semibold text-primary">Quirófano disponible</label>
                    <select id="surgery-space" name="espacio_id" required class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                        <option value="">Selecciona un quirófano</option>
                        @foreach ($espaciosDisponibles->where('tipo', 'quirofano') as $space)
                            <option value="{{ $space->id }}" @selected(old('espacio_id') == $space->id)>{{ $space->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="surgery-start" class="text-sm font-semibold text-primary">Fecha y hora</label>
                    <input id="surgery-start" type="datetime-local" name="programada_at" value="{{ old('programada_at') }}" required class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                </div>
                <div class="flex flex-col gap-2">
                    <label for="surgeon" class="text-sm font-semibold text-primary">Cirujano</label>
                    <select id="surgeon" name="cirujano_id" required class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                        <option value="">Selecciona un profesional</option>
                        @foreach ($profesionales as $professional)
                            <option value="{{ $professional->id }}" @selected(old('cirujano_id') == $professional->id)>{{ $professional->nombre }} {{ $professional->apellido }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="anesthesiologist" class="text-sm font-semibold text-primary">Anestesiólogo</label>
                    <select id="anesthesiologist" name="anestesiologo_id" required class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                        <option value="">Selecciona un profesional distinto</option>
                        @foreach ($profesionales as $professional)
                            <option value="{{ $professional->id }}" @selected(old('anestesiologo_id') == $professional->id)>{{ $professional->nombre }} {{ $professional->apellido }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="surgery-duration" class="text-sm font-semibold text-primary">Duración (minutos)</label>
                    <input id="surgery-duration" type="number" name="duracion_minutos" value="{{ old('duracion_minutos', 60) }}" min="15" max="720" step="15" required class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                </div>
                <label class="flex items-start gap-3 rounded-xl bg-surface-container-low p-3 text-sm text-on-surface sm:col-span-2">
                    <input type="checkbox" name="consentimiento_firmado" value="1" @checked(old('consentimiento_firmado')) required class="mt-0.5 size-4 accent-primary">
                    <span>Confirmo que el propietario asociado firmó el consentimiento y que el documento fue verificado.</span>
                </label>
                <div class="flex flex-col gap-2 sm:col-span-2">
                    <label for="surgery-notes" class="text-sm font-semibold text-primary">Observaciones</label>
                    <textarea id="surgery-notes" name="observaciones" rows="2" maxlength="2000" class="rounded-lg border border-outline-variant bg-surface px-3 py-2 text-on-surface">{{ old('observaciones') }}</textarea>
                </div>
                <button type="submit" class="min-h-11 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-on-primary hover:bg-primary/90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary sm:col-span-2">
                    Programar y reservar horarios
                </button>
            </form>
        </section>

        <section aria-labelledby="admission-heading" class="rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:p-6">
            <h2 id="admission-heading" class="text-lg font-bold text-primary">Ingresar a hospitalización</h2>
            <p class="mt-1 text-sm leading-6 text-on-surface-variant">Solo se muestran atenciones con recomendación médica, nota previa y espacio disponible.</p>
            <form method="POST" action="{{ route('portal.hospitalizaciones.store') }}" class="mt-5 grid gap-4">
                @csrf
                <div class="flex flex-col gap-2">
                    <label for="admission-atencion" class="text-sm font-semibold text-primary">Paciente y atención</label>
                    <select id="admission-atencion" name="atencion_id" required class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                        <option value="">Selecciona una atención recomendada</option>
                        @foreach ($admissionAttentions as $attention)
                            <option value="{{ $attention->id }}" @selected(old('atencion_id') == $attention->id)>
                                {{ $attention->mascota->nombre }} · {{ $attention->propietario->nombre }} · {{ $attention->folio }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="admission-space" class="text-sm font-semibold text-primary">Cama disponible</label>
                    <select id="admission-space" name="espacio_id" required class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                        <option value="">Selecciona una cama</option>
                        @foreach ($espaciosDisponibles->where('tipo', 'hospitalizacion') as $space)
                            <option value="{{ $space->id }}" @selected(old('espacio_id') == $space->id)>{{ $space->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="min-h-11 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-on-primary hover:bg-primary/90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                    Registrar ingreso
                </button>
            </form>
        </section>
    </div>

    <section aria-labelledby="active-hospitalizations-heading" class="mt-8 rounded-2xl border border-outline-variant/50 bg-surface-container-lowest shadow-sm">
        <div class="border-b border-outline-variant/40 px-5 py-4 sm:px-6">
            <h2 id="active-hospitalizations-heading" class="text-lg font-bold text-primary">Pacientes hospitalizados</h2>
            <p class="mt-1 text-sm text-on-surface-variant">Cada monitoreo debe registrarse en intervalos de cuatro horas y agregar una nota al expediente.</p>
        </div>
        @forelse ($hospitalizaciones as $hospitalization)
            <article
                data-monitoring-card
                data-monitoring-next-at="{{ $hospitalization->proximoMonitoreoAt()->toIso8601String() }}"
                class="grid gap-5 border-b border-outline-variant/40 px-5 py-5 last:border-b-0 sm:px-6 lg:grid-cols-[minmax(0,1fr)_minmax(20rem,1fr)]"
            >
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="font-bold text-on-surface">{{ $hospitalization->atencion->mascota->nombre }}</h3>
                        <span class="rounded-full bg-secondary-container px-3 py-1 text-xs font-semibold text-on-secondary-container">{{ $hospitalization->folio }}</span>
                        <span data-monitoring-due-badge @if (! $hospitalization->monitoreoVencido()) hidden @endif class="rounded-full bg-error-container px-3 py-1 text-xs font-bold text-on-error-container">Monitoreo vencido</span>
                    </div>
                    <p class="mt-2 text-sm text-on-surface-variant">
                        {{ $hospitalization->espacio->nombre }} · Ingreso {{ $hospitalization->ingresada_at->format('d/m/Y H:i') }}
                    </p>
                    <p class="mt-1 text-sm text-on-surface-variant">Propietario: {{ $hospitalization->atencion->propietario->nombre }}</p>
                    @if ($hospitalization->ultimoMonitoreo)
                        <p class="mt-3 text-sm text-on-surface-variant">
                            Último monitoreo: {{ $hospitalization->ultimoMonitoreo->registrada_at->format('d/m/Y H:i') }}
                        </p>
                    @else
                        <p class="mt-3 text-sm text-on-surface-variant">Próximo monitoreo: {{ $hospitalization->proximoMonitoreoAt()->format('d/m/Y H:i') }}</p>
                    @endif
                    @if ($hospitalization->monitoreos->isNotEmpty())
                        <ul class="mt-3 grid gap-2 text-sm text-on-surface-variant">
                            @foreach ($hospitalization->monitoreos->sortByDesc('registrada_at')->take(3) as $monitoring)
                                <li class="rounded-lg bg-surface-container-low p-3">
                                    <span class="font-semibold">{{ $monitoring->registrada_at->format('d/m/Y H:i') }}:</span>
                                    {{ $monitoring->nota_progreso }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="grid content-start gap-4">
                    @if ($hospitalization->monitoreoVencido())
                        <form method="POST" action="{{ route('portal.hospitalizaciones.monitoreos.store', $hospitalization) }}" class="grid gap-3 rounded-xl bg-surface-container-low p-4 sm:grid-cols-2">
                            @csrf
                            <h4 class="font-bold text-primary sm:col-span-2">Registrar monitoreo</h4>
                            <div class="flex flex-col gap-2">
                                <label for="temperature-{{ $hospitalization->id }}" class="text-xs font-semibold text-primary">Temperatura (°C)</label>
                                <input id="temperature-{{ $hospitalization->id }}" type="number" name="temperatura_c" min="20" max="50" step="0.01" required class="min-h-10 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                            </div>
                            <div class="flex flex-col gap-2">
                                <label for="heart-rate-{{ $hospitalization->id }}" class="text-xs font-semibold text-primary">Frecuencia cardiaca</label>
                                <input id="heart-rate-{{ $hospitalization->id }}" type="number" name="frecuencia_cardiaca" min="1" max="500" class="min-h-10 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                            </div>
                            <div class="flex flex-col gap-2 sm:col-span-2">
                                <label for="treatment-{{ $hospitalization->id }}" class="text-xs font-semibold text-primary">Tratamiento administrado</label>
                                <textarea id="treatment-{{ $hospitalization->id }}" name="tratamiento" rows="2" maxlength="5000" required class="rounded-lg border border-outline-variant bg-surface px-3 py-2 text-on-surface"></textarea>
                            </div>
                            <div class="flex flex-col gap-2 sm:col-span-2">
                                <label for="progress-{{ $hospitalization->id }}" class="text-xs font-semibold text-primary">Nota de progreso</label>
                                <textarea id="progress-{{ $hospitalization->id }}" name="nota_progreso" rows="3" maxlength="10000" required class="rounded-lg border border-outline-variant bg-surface px-3 py-2 text-on-surface"></textarea>
                            </div>
                            <button type="submit" class="min-h-10 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-on-primary sm:col-span-2">Guardar monitoreo y nota clínica</button>
                        </form>
                    @else
                        <p class="rounded-xl bg-surface-container-low p-4 text-sm text-on-surface-variant">
                            Próximo monitoreo a partir de {{ $hospitalization->proximoMonitoreoAt()->format('d/m/Y H:i') }}.
                        </p>
                    @endif

                    @if ($hospitalization->monitoreos->contains(fn ($monitoring): bool => filled($monitoring->nota_progreso)))
                        <form method="POST" action="{{ route('portal.hospitalizaciones.alta', $hospitalization) }}">
                            @csrf
                            <button type="submit" class="min-h-10 w-full rounded-lg border border-error/40 px-4 py-2 text-sm font-bold text-error hover:bg-error-container focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                                Registrar alta y enviar espacio a desinfección
                            </button>
                        </form>
                    @else
                        <p class="text-sm text-on-surface-variant">El alta estará disponible después de registrar una nota de progreso.</p>
                    @endif
                </div>
            </article>
        @empty
            <p class="px-5 py-8 text-center text-sm text-on-surface-variant">No hay pacientes hospitalizados en este momento.</p>
        @endforelse
    </section>

    <section aria-labelledby="scheduled-surgeries-heading" class="mt-8 rounded-2xl border border-outline-variant/50 bg-surface-container-lowest shadow-sm">
        <div class="border-b border-outline-variant/40 px-5 py-4 sm:px-6">
            <h2 id="scheduled-surgeries-heading" class="text-lg font-bold text-primary">Cirugías programadas</h2>
            <p class="mt-1 text-sm text-on-surface-variant">Al cerrar cada cirugía se descuentan los insumos por lote FIFO y el quirófano queda pendiente de desinfección.</p>
        </div>
        @forelse ($cirugias as $surgery)
            <article class="grid gap-5 border-b border-outline-variant/40 px-5 py-5 last:border-b-0 sm:px-6 lg:grid-cols-[minmax(0,1fr)_minmax(20rem,1fr)]">
                <div>
                    <h3 class="font-bold text-on-surface">{{ $surgery->atencion->mascota->nombre }} · {{ $surgery->folio }}</h3>
                    <p class="mt-2 text-sm text-on-surface-variant">
                        {{ $surgery->programada_at->format('d/m/Y H:i') }} · {{ $surgery->duracion_minutos }} minutos · {{ $surgery->espacio->nombre }}
                    </p>
                    <p class="mt-1 text-sm text-on-surface-variant">
                        Cirujano: {{ $surgery->cirujano->nombre }} {{ $surgery->cirujano->apellido }} · Anestesiólogo: {{ $surgery->anestesiologo->nombre }} {{ $surgery->anestesiologo->apellido }}
                    </p>
                </div>
                @if (now()->greaterThanOrEqualTo($surgery->programada_at))
                    <form method="POST" action="{{ route('portal.cirugias.completar', $surgery) }}" class="grid gap-3 rounded-xl bg-surface-container-low p-4">
                        @csrf
                        <h4 class="font-bold text-primary">Cerrar cirugía y registrar insumos</h4>
                        @foreach (range(0, 2) as $index)
                            <div class="grid gap-2 sm:grid-cols-[minmax(0,1fr)_8rem]">
                                <label class="sr-only" for="supply-{{ $surgery->id }}-{{ $index }}">Insumo {{ $index + 1 }}</label>
                                <select id="supply-{{ $surgery->id }}-{{ $index }}" name="insumos[{{ $index }}][producto_id]" class="min-h-10 rounded-lg border border-outline-variant bg-surface px-3 text-sm text-on-surface">
                                    <option value="">Sin insumo adicional</option>
                                    @foreach ($productos as $product)
                                        <option value="{{ $product->id }}">{{ $product->nombre }} ({{ $product->unidad_base }})</option>
                                    @endforeach
                                </select>
                                <label class="sr-only" for="quantity-{{ $surgery->id }}-{{ $index }}">Cantidad del insumo {{ $index + 1 }}</label>
                                <input id="quantity-{{ $surgery->id }}-{{ $index }}" type="number" name="insumos[{{ $index }}][cantidad]" min="0.001" max="1000000" step="0.001" placeholder="Cantidad" class="min-h-10 rounded-lg border border-outline-variant bg-surface px-3 text-sm text-on-surface">
                            </div>
                        @endforeach
                        <button type="submit" class="min-h-10 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-on-primary">Completar cirugía</button>
                    </form>
                @else
                    <p class="rounded-xl bg-surface-container-low p-4 text-sm text-on-surface-variant">El cierre estará disponible después de la hora programada.</p>
                @endif
            </article>
        @empty
            <p class="px-5 py-8 text-center text-sm text-on-surface-variant">No hay cirugías programadas.</p>
        @endforelse
    </section>

    <section aria-labelledby="clinical-spaces-heading" class="mt-8 rounded-2xl border border-outline-variant/50 bg-surface-container-lowest shadow-sm">
        <div class="border-b border-outline-variant/40 px-5 py-4 sm:px-6">
            <h2 id="clinical-spaces-heading" class="text-lg font-bold text-primary">Espacios clínicos</h2>
            <p class="mt-1 text-sm text-on-surface-variant">Los quirófanos y camas solo vuelven a estar disponibles después de registrar su desinfección.</p>
        </div>
        @if ($esAdministrador)
            <form method="POST" action="{{ route('portal.espacios.store') }}" class="grid gap-4 border-b border-outline-variant/40 px-5 py-5 sm:grid-cols-[minmax(0,1fr)_minmax(12rem,0.6fr)_auto] sm:items-end sm:px-6">
                @csrf
                <div class="flex flex-col gap-2">
                    <label for="space-name" class="text-sm font-semibold text-primary">Nombre del espacio</label>
                    <input id="space-name" name="nombre" value="{{ old('nombre') }}" maxlength="150" required class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                </div>
                <div class="flex flex-col gap-2">
                    <label for="space-type" class="text-sm font-semibold text-primary">Tipo</label>
                    <select id="space-type" name="tipo" required class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-on-surface">
                        <option value="quirofano" @selected(old('tipo') === 'quirofano')>Quirófano</option>
                        <option value="hospitalizacion" @selected(old('tipo') === 'hospitalizacion')>Hospitalización</option>
                    </select>
                </div>
                <button type="submit" class="min-h-11 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-on-primary">Agregar espacio</button>
            </form>
        @endif
        <div class="divide-y divide-outline-variant/40">
            @forelse ($espacios as $space)
                <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <p class="font-semibold text-on-surface">{{ $space->nombre }}</p>
                        <p class="mt-1 text-sm text-on-surface-variant">
                            {{ $space->tipo === 'quirofano' ? 'Quirófano' : 'Hospitalización' }}
                            · Estado:
                            <span class="font-semibold">{{ ucfirst($space->estado) }}</span>
                            @if ($space->desinfectado_at)
                                · Desinfectado {{ $space->desinfectado_at->format('d/m/Y H:i') }}
                            @endif
                        </p>
                    </div>
                    @if ($space->estado === 'limpieza')
                        <form method="POST" action="{{ route('portal.espacios.desinfectar', $space) }}">
                            @csrf
                            <button type="submit" class="min-h-10 rounded-lg bg-secondary-container px-4 py-2 text-sm font-bold text-on-secondary-container">Registrar desinfección</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-on-surface-variant">Todavía no hay espacios clínicos registrados.</p>
            @endforelse
        </div>
    </section>

    @if ($hospitalizaciones->isNotEmpty())
        <script>
            const alarmButton = document.getElementById('monitoring-alarm');
            const monitoringAlert = document.getElementById('monitoring-alert');
            const monitoringAlertCount = document.getElementById('monitoring-alert-count');
            const monitoringCards = Array.from(document.querySelectorAll('[data-monitoring-card]'));
            let alarmTimer = null;
            let audioContext = null;

            const updateMonitoringAlerts = () => {
                const now = Date.now();
                let dueCount = 0;

                monitoringCards.forEach((card) => {
                    const isDue = Date.parse(card.dataset.monitoringNextAt) <= now;
                    const badge = card.querySelector('[data-monitoring-due-badge]');
                    badge.hidden = !isDue;
                    dueCount += Number(isDue);
                });

                monitoringAlert.style.display = dueCount === 0 ? 'none' : '';
                monitoringAlertCount.textContent = String(dueCount);
            };

            alarmButton.addEventListener('click', async () => {
                if (alarmTimer) {
                    window.clearInterval(alarmTimer);
                    alarmTimer = null;
                    alarmButton.textContent = 'Activar alarma sonora';
                    return;
                }

                audioContext ??= new AudioContext();
                await audioContext.resume();

                const playTone = () => {
                    const oscillator = audioContext.createOscillator();
                    const volume = audioContext.createGain();
                    oscillator.frequency.value = 880;
                    volume.gain.value = 0.12;
                    oscillator.connect(volume);
                    volume.connect(audioContext.destination);
                    oscillator.start();
                    oscillator.stop(audioContext.currentTime + 0.35);
                };

                playTone();
                alarmTimer = window.setInterval(playTone, 15000);
                alarmButton.textContent = 'Desactivar alarma sonora';
            });

            updateMonitoringAlerts();
            window.setInterval(updateMonitoringAlerts, 15000);
        </script>
    @endif
@endsection
