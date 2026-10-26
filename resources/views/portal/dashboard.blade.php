@extends('layouts.portal')

@section('title', 'Resumen')
@section('portal-section', 'Inicio')
@section('page-heading', 'Resumen del portal')
@section('meta-description', 'Panel privado para la gestión diaria de Patitas Felices.')

@section('portal-navigation')
    <a href="#resumen" aria-current="page" class="flex min-h-11 items-center gap-3 rounded-lg bg-primary-container px-3 text-sm font-semibold text-on-primary">
        <span class="material-symbols-outlined text-xl" aria-hidden="true">space_dashboard</span>
        Resumen
    </a>
    <a href="#pacientes" class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-semibold text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
        <span class="material-symbols-outlined text-xl" aria-hidden="true">pets</span>
        Pacientes
    </a>
    <a href="#citas" class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-semibold text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
        <span class="material-symbols-outlined text-xl" aria-hidden="true">calendar_month</span>
        Citas
    </a>
    <a href="#inventario" class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-semibold text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
        <span class="material-symbols-outlined text-xl" aria-hidden="true">inventory_2</span>
        Inventario
    </a>
@endsection

@section('portal-mobile-navigation')
    <a href="#resumen" aria-current="page" class="shrink-0 rounded-full bg-primary-container px-4 py-2 text-sm font-semibold text-on-primary">Resumen</a>
    <a href="#pacientes" class="shrink-0 rounded-full bg-surface-container px-4 py-2 text-sm font-semibold text-primary">Pacientes</a>
    <a href="#citas" class="shrink-0 rounded-full bg-surface-container px-4 py-2 text-sm font-semibold text-primary">Citas</a>
    <a href="#inventario" class="shrink-0 rounded-full bg-surface-container px-4 py-2 text-sm font-semibold text-primary">Inventario</a>
@endsection

@section('breadcrumbs')
    <nav aria-label="Ruta de navegación" class="mb-6 text-sm text-on-surface-variant">
        <span aria-current="page" class="font-semibold text-primary">Inicio</span>
    </nav>
@endsection

@section('content')
    <section id="resumen" class="flex flex-col gap-2">
        <p class="text-sm font-semibold text-on-surface-variant">{{ now()->format('d/m/Y') }} · Vista general</p>
        <p class="max-w-2xl leading-7 text-on-surface-variant">Consulta aquí la actividad de la clínica. Conecta estos bloques con los datos que proporcione cada controlador.</p>
    </section>

    <section aria-label="Indicadores del portal" class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['icon' => 'pets', 'label' => 'Pacientes registrados', 'value' => '—', 'description' => 'Conecta el total de pacientes'],
            ['icon' => 'event_available', 'label' => 'Citas de hoy', 'value' => '—', 'description' => 'Conecta la agenda diaria'],
            ['icon' => 'vaccines', 'label' => 'Vacunas pendientes', 'value' => '—', 'description' => 'Conecta los seguimientos'],
            ['icon' => 'inventory_2', 'label' => 'Alertas de inventario', 'value' => '—', 'description' => 'Conecta las existencias'],
        ] as $metric)
            <article class="flex min-h-40 flex-col justify-between gap-space-md rounded-card border border-outline-variant/50 bg-surface-container-lowest p-space-md shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <span class="grid size-11 place-items-center rounded-xl bg-surface-container text-primary">
                        <span class="material-symbols-outlined text-2xl" aria-hidden="true">{{ $metric['icon'] }}</span>
                    </span>
                    <span class="rounded-full bg-surface-container px-3 py-1 text-xs font-semibold text-on-surface-variant">Resumen</span>
                </div>
                <div class="flex flex-col gap-1">
                    <p class="text-sm font-semibold text-on-surface-variant">{{ $metric['label'] }}</p>
                    <p class="text-3xl font-bold tracking-tight text-primary">{{ $metric['value'] }}</p>
                    <p class="text-xs text-on-surface-variant">{{ $metric['description'] }}</p>
                </div>
            </article>
        @endforeach
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(18rem,0.6fr)]">
        <article id="citas" class="overflow-hidden rounded-2xl border border-outline-variant/50 bg-surface-container-lowest shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-outline-variant/40 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="font-bold text-primary">Agenda de hoy</h2>
                    <p class="mt-1 text-sm text-on-surface-variant">Conecta aquí las citas desde el controlador.</p>
                </div>
                <span class="material-symbols-outlined text-2xl text-secondary" aria-hidden="true">calendar_month</span>
            </div>
            <div class="grid min-h-40 place-items-center px-5 py-8 text-center sm:px-6">
                <p class="max-w-sm leading-7 text-on-surface-variant">Todavía no hay datos enlazados. Sustituye este estado por la lista de citas y sus estados.</p>
            </div>
        </article>

        <article id="pacientes" class="flex flex-col gap-4 rounded-2xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm sm:p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="font-bold text-primary">Accesos rápidos</h2>
                    <p class="mt-1 text-sm text-on-surface-variant">Atajos para tareas frecuentes</p>
                </div>
                <span class="material-symbols-outlined text-2xl text-secondary" aria-hidden="true">bolt</span>
            </div>
            <a href="#pacientes" class="flex min-h-12 items-center justify-between gap-3 rounded-lg bg-surface-container-low px-4 py-3 text-sm font-semibold text-primary transition-colors hover:bg-surface-container focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                Consultar pacientes
                <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
            </a>
            <a href="#citas" class="flex min-h-12 items-center justify-between gap-3 rounded-lg bg-surface-container-low px-4 py-3 text-sm font-semibold text-primary transition-colors hover:bg-surface-container focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                Revisar la agenda
                <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
            </a>
            <a id="inventario" href="#inventario" class="flex min-h-12 items-center justify-between gap-3 rounded-lg bg-surface-container-low px-4 py-3 text-sm font-semibold text-primary transition-colors hover:bg-surface-container focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                Ver inventario
                <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
            </a>
        </article>
    </section>
@endsection
