@extends('layouts.public')

@section('title', 'Cuidado veterinario con cariño')
@section('meta-description', 'Conoce Patitas Felices: atención veterinaria cercana para el bienestar de tus mascotas.')

@section('public-navigation')
    <a href="#servicios" class="rounded-md text-sm font-semibold text-on-surface-variant transition-colors hover:text-primary focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-secondary">Servicios</a>
    <a href="#equipo" class="rounded-md text-sm font-semibold text-on-surface-variant transition-colors hover:text-primary focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-secondary">Nuestro cuidado</a>
    <a href="#contacto" class="rounded-md text-sm font-semibold text-on-surface-variant transition-colors hover:text-primary focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-secondary">Contacto</a>
@endsection

@section('mobile-navigation')
    <a href="#servicios" class="shrink-0 rounded-full bg-surface-container px-4 py-2 text-sm font-semibold text-primary">Servicios</a>
    <a href="#equipo" class="shrink-0 rounded-full bg-surface-container px-4 py-2 text-sm font-semibold text-primary">Nuestro cuidado</a>
    <a href="#contacto" class="shrink-0 rounded-full bg-surface-container px-4 py-2 text-sm font-semibold text-primary">Contacto</a>
@endsection

@section('header-actions')
    <a href="#contacto" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-primary-container px-4 py-2.5 text-sm font-semibold text-on-primary transition-colors hover:bg-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
        <span>Agenda una consulta</span>
        <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
    </a>
@endsection

@section('content')
    <section class="overflow-hidden px-4 py-12 sm:px-6 sm:py-16 lg:px-8 lg:py-24">
        <div class="mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-[minmax(0,1.2fr)_minmax(20rem,0.8fr)] lg:gap-16">
            <div class="flex flex-col items-start gap-6">
                <span class="inline-flex items-center gap-2 rounded-full bg-secondary-container/60 px-4 py-2 text-sm font-semibold text-on-secondary-container">
                    <span class="material-symbols-outlined text-lg" aria-hidden="true">favorite</span>
                    Cuidado cercano en cada etapa
                </span>
                <h1 class="max-w-3xl text-4xl font-bold leading-tight tracking-tight text-primary sm:text-5xl lg:text-6xl">
                    Su bienestar también es parte de tu familia.
                </h1>
                <p class="max-w-2xl text-lg leading-8 text-on-surface-variant">
                    Un espacio pensado para cuidar la salud de tus mascotas con atención profesional, amable y clara.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="#contacto" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-lg bg-primary-container px-5 py-3 font-semibold text-on-primary shadow-sm transition-colors hover:bg-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                        Solicitar una cita
                        <span class="material-symbols-outlined" aria-hidden="true">calendar_month</span>
                    </a>
                    <a href="#servicios" class="inline-flex min-h-12 items-center justify-center rounded-lg border border-outline-variant bg-surface-container-lowest px-5 py-3 font-semibold text-primary transition-colors hover:bg-surface-container-low focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                        Conocer los servicios
                    </a>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -right-8 -top-8 size-40 rounded-full bg-secondary-container/60 blur-2xl" aria-hidden="true"></div>
                <div class="relative grid min-h-80 place-items-center overflow-hidden rounded-[2rem] bg-surface-container p-8 shadow-[0_20px_32px_-8px_rgba(44,58,140,0.14)] sm:min-h-96">
                    <div class="flex max-w-sm flex-col items-center gap-5 text-center">
                        <span class="grid size-24 place-items-center rounded-full bg-surface-container-lowest text-primary shadow-lg">
                            <span class="material-symbols-outlined text-5xl" aria-hidden="true">pets</span>
                        </span>
                        <p class="text-2xl font-bold text-primary">Un cuidado que se siente en casa</p>
                        <p class="leading-7 text-on-surface-variant">Acompañamos a cada mascota y a quienes la quieren.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="servicios" class="bg-surface-container-low px-4 py-14 sm:px-6 sm:py-16 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 flex flex-col gap-3">
                <p class="text-sm font-bold uppercase tracking-widest text-secondary">Nuestros servicios</p>
                <h2 class="text-3xl font-bold tracking-tight text-primary sm:text-4xl">Atención para una vida más saludable</h2>
                <p class="max-w-2xl leading-7 text-on-surface-variant">Esta sección es una plantilla: personaliza los servicios disponibles según la atención que ofrece tu clínica.</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['icon' => 'stethoscope', 'title' => 'Consulta veterinaria', 'description' => 'Valoración y orientación para acompañar la salud de tu mascota.'],
                    ['icon' => 'vaccines', 'title' => 'Prevención y vacunas', 'description' => 'Seguimiento preventivo adaptado a cada etapa de vida.'],
                    ['icon' => 'health_metrics', 'title' => 'Bienestar integral', 'description' => 'Atención cercana y recomendaciones para su cuidado diario.'],
                ] as $service)
                    <article class="flex flex-col gap-space-md rounded-card border border-outline-variant/50 bg-surface-container-lowest p-space-lg shadow-sm">
                        <span class="grid size-12 place-items-center rounded-xl bg-surface-container text-primary">
                            <span class="material-symbols-outlined text-2xl" aria-hidden="true">{{ $service['icon'] }}</span>
                        </span>
                        <h3 class="text-lg font-bold text-primary">{{ $service['title'] }}</h3>
                        <p class="leading-7 text-on-surface-variant">{{ $service['description'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="equipo" class="px-4 py-14 sm:px-6 sm:py-16 lg:px-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-5 rounded-3xl bg-primary-container px-6 py-10 text-on-primary sm:px-10 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex max-w-2xl flex-col gap-3">
                <p class="text-sm font-bold uppercase tracking-widest text-on-primary-container">Nuestro compromiso</p>
                <h2 class="text-3xl font-bold tracking-tight">Atención profesional, trato amable.</h2>
                <p class="leading-7 text-on-primary/80">Personaliza este espacio con la historia, el equipo y los valores de tu clínica.</p>
            </div>
            <a href="#contacto" class="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 self-start rounded-lg bg-surface-container-lowest px-5 py-3 font-semibold text-primary transition-colors hover:bg-surface-container focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-on-primary lg:self-auto">
                Conócenos
                <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
            </a>
        </div>
    </section>
@endsection
