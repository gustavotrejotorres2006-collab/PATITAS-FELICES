<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="@yield('meta-description', 'Atención veterinaria cercana y profesional para cada etapa de la vida de tu mascota.')">

        <title>@yield('title', 'Patitas Felices') · Patitas Felices</title>

        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400,0,0&display=swap" rel="stylesheet">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="min-h-screen bg-surface font-sans text-on-surface antialiased">
        <a href="#contenido" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-surface-container-lowest focus:px-4 focus:py-3 focus:text-primary focus:shadow-lg">
            Saltar al contenido
        </a>

        <header class="border-b border-outline-variant/50 bg-surface-container-lowest">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3 rounded-lg text-primary focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-secondary">
                    <span class="grid size-11 place-items-center rounded-xl bg-primary-container text-on-primary">
                        <span class="material-symbols-outlined text-2xl" aria-hidden="true">pets</span>
                    </span>
                    <span class="leading-tight">
                        <span class="block text-lg font-bold tracking-tight">Patitas Felices</span>
                        <span class="block text-xs font-medium text-on-surface-variant">Cuidado veterinario</span>
                    </span>
                </a>

                <nav aria-label="Navegación principal" class="hidden items-center gap-7 md:flex">
                    @yield('public-navigation')
                </nav>

                <div class="flex shrink-0 items-center gap-3">
                    @yield('header-actions')
                    @hasSection('header-actions')
                    @else
                        <a href="#contacto" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-primary-container px-4 py-2.5 text-sm font-semibold text-on-primary transition-colors hover:bg-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                            <span>Contáctanos</span>
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
                        </a>
                    @endif
                </div>
            </div>
            <nav aria-label="Navegación móvil" class="flex gap-2 overflow-x-auto border-t border-outline-variant/40 px-4 py-2 md:hidden">
                @yield('mobile-navigation')
            </nav>
        </header>

        <main id="contenido">
            @yield('content')
        </main>

        <footer id="contacto" class="border-t border-outline-variant/50 bg-surface-container-lowest">
            <div class="mx-auto flex max-w-7xl flex-col gap-5 px-4 py-8 text-sm text-on-surface-variant sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
                <a href="{{ route('home') }}" class="font-semibold text-primary">Patitas Felices</a>
                <p>Atención veterinaria cercana para quienes llenan tu vida de alegría.</p>
                <p>&copy; {{ now()->year }} Todos los derechos reservados.</p>
            </div>
        </footer>
    </body>
</html>
