@extends('layouts.portal')

@section('title', 'Agenda de citas')
@section('portal-section', 'Atención médica')
@section('page-heading', 'Agenda de citas')
@section('meta-description', 'Consulta, programa y administra las citas de la clínica veterinaria.')

@section('breadcrumbs')
    <nav aria-label="Ruta de navegación" class="mb-6 text-sm text-on-surface-variant">
        <span aria-current="page" class="font-semibold text-primary">Agenda de citas</span>
    </nav>
@endsection

@section('portal-header-actions')
    <a href="{{ route('portal.citas.create') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-primary-container px-4 py-2 text-sm font-semibold text-on-primary transition-colors hover:bg-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
        <span class="material-symbols-outlined text-lg" aria-hidden="true">add</span>
        <span class="hidden sm:inline">Nueva cita</span>
    </a>
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

    <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm leading-6 text-on-surface-variant">Prioridad al programar: urgencia, cirugía, revisión, limpieza dental y corte o baño.</p>
            <p class="mt-1 text-sm leading-6 text-on-surface-variant">Las citas fuera del horario regular de 8:00 a 20:00 incluyen un recargo del 30%.</p>
        </div>
        <form method="GET" action="{{ route('portal.citas.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex flex-col gap-2">
                <label for="fecha" class="text-sm font-semibold text-primary">Filtrar por fecha</label>
                <input id="fecha" name="fecha" type="date" value="{{ $fecha }}" class="min-h-11 rounded-lg border border-outline-variant bg-surface px-3 text-sm text-on-surface outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/30">
            </div>
            <button type="submit" class="min-h-11 rounded-lg border border-outline-variant px-4 text-sm font-semibold text-primary transition-colors hover:bg-surface-container-low focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                Aplicar
            </button>
            @if ($fecha !== '')
                <a href="{{ route('portal.citas.index') }}" class="min-h-11 content-center rounded-lg px-3 text-sm font-semibold text-secondary underline">Quitar filtro</a>
            @endif
        </form>
    </section>

    <section aria-label="Citas programadas" class="mt-6 overflow-hidden rounded-2xl border border-outline-variant/50 bg-surface-container-lowest shadow-sm">
        @if ($citas->isEmpty())
            <div class="grid min-h-48 place-items-center px-6 py-10 text-center">
                <div>
                    <span class="material-symbols-outlined text-4xl text-secondary" aria-hidden="true">event_busy</span>
                    <p class="mt-2 font-semibold text-primary">No hay citas para mostrar</p>
                    <p class="mt-1 text-sm text-on-surface-variant">Prueba otra fecha o agrega una cita a la agenda.</p>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[52rem] border-collapse text-left text-sm">
                    <thead class="bg-surface-container-low text-xs uppercase tracking-wide text-on-surface-variant">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold">Fecha y hora</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Paciente y propietario</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Servicio</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Profesional</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Total</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Estado</th>
                            <th scope="col" class="px-5 py-3 font-semibold"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/40">
                        @foreach ($citas as $cita)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <p class="font-semibold text-on-surface">{{ $cita->inicio->format('d/m/Y H:i') }}</p>
                                    <p class="mt-1 text-xs text-on-surface-variant">Hasta {{ $cita->fin->format('H:i') }}</p>
                                    @if ($cita->urgente)
                                        <span class="mt-2 inline-flex rounded-full bg-error-container px-2.5 py-1 text-xs font-semibold text-on-error-container">Urgencia</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-on-surface">{{ $cita->mascota->nombre }}</p>
                                    <p class="mt-1 text-xs text-on-surface-variant">{{ $cita->propietario?->nombre ?? 'Sin propietario' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    @foreach ($cita->servicios as $servicio)
                                        <p>{{ $servicio->nombre }}</p>
                                    @endforeach
                                </td>
                                <td class="px-5 py-4">{{ $cita->profesional ? trim($cita->profesional->nombre.' '.$cita->profesional->apellido) : 'Sin asignar' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 font-semibold text-on-surface">
                                    ${{ number_format($cita->servicios->sum(fn ($servicio) => (float) $servicio->pivot->precio), 2) }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-surface-container px-3 py-1 text-xs font-semibold text-on-surface-variant">
                                        {{ ucfirst($cita->estado) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if (in_array($cita->estado, ['pendiente', 'confirmada'], true))
                                        <form method="POST" action="{{ route('portal.citas.cancelar', $cita) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg px-3 py-2 text-xs font-semibold text-error transition-colors hover:bg-error-container focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                                                Cancelar
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-outline-variant/40 px-5 py-4">
                {{ $citas->links() }}
            </div>
        @endif
    </section>
@endsection
