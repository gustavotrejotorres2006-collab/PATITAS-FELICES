<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="@yield('meta-description', 'Portal privado de gestión veterinaria de Patitas Felices.')">

        <title>@yield('title', 'Portal') · Patitas Felices</title>

        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400,0,0&display=swap" rel="stylesheet">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="min-h-screen bg-surface font-sans text-on-surface antialiased">
        <a href="#contenido" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-surface-container-lowest focus:px-4 focus:py-3 focus:text-primary focus:shadow-lg">
            Saltar al contenido
        </a>

        <div class="min-h-screen md:grid md:grid-cols-[17rem_minmax(0,1fr)]">
            <aside class="hidden border-r border-outline-variant/50 bg-surface-container-lowest md:flex md:flex-col">
                <a href="{{ route('home') }}" class="flex items-center gap-3 border-b border-outline-variant/40 px-6 py-5 text-primary focus-visible:outline-2 focus-visible:outline-inset focus-visible:outline-secondary">
                    <span class="grid size-11 place-items-center rounded-xl bg-primary-container text-on-primary">
                        <span class="material-symbols-outlined text-2xl" aria-hidden="true">pets</span>
                    </span>
                    <span class="leading-tight">
                        <span class="block text-lg font-bold tracking-tight">Patitas Felices</span>
                        <span class="block text-xs font-medium text-on-surface-variant">Portal de gestión</span>
                    </span>
                </a>

                <nav aria-label="Navegación del portal" class="flex-1 space-y-1 px-4 py-6">
                    @yield('portal-navigation')
                </nav>

                <div class="border-t border-outline-variant/40 p-4">
                    @auth
                        <div class="flex items-center gap-3 rounded-xl bg-surface-container-low p-3">
                            <span class="grid size-10 shrink-0 place-items-center rounded-full bg-secondary-container font-bold text-on-secondary-container" aria-hidden="true">
                                {{ str(auth()->user()->name)->substr(0, 1)->upper() }}
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold">{{ auth()->user()->name }}</span>
                                <span class="block text-xs text-on-surface-variant">Cuenta del portal</span>
                            </span>
                        </div>
                        @if (Route::has('logout'))
                            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                                @csrf
                                <button type="submit" class="flex min-h-11 w-full items-center gap-3 rounded-lg px-3 text-sm font-semibold text-on-surface-variant transition-colors hover:bg-error-container hover:text-on-error-container focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                                    <span class="material-symbols-outlined text-xl" aria-hidden="true">logout</span>
                                    Cerrar sesión
                                </button>
                            </form>
                        @endif
                    @endauth
                </div>
            </aside>

            <div class="flex min-h-screen min-w-0 flex-col">
                <header class="sticky top-0 z-20 border-b border-outline-variant/50 bg-surface-container-lowest/95 backdrop-blur">
                    <div class="flex min-h-16 items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                        <div class="flex min-w-0 items-center gap-3">
                            <a href="{{ route('home') }}" aria-label="Ir al sitio público" class="grid size-10 shrink-0 place-items-center rounded-xl bg-primary-container text-on-primary md:hidden">
                                <span class="material-symbols-outlined" aria-hidden="true">pets</span>
                            </a>
                            <div class="min-w-0">
                                <p class="truncate text-xs font-semibold uppercase tracking-wider text-on-surface-variant">
                                    @yield('portal-section', 'Área privada')
                                </p>
                                <h1 class="truncate text-lg font-bold tracking-tight text-primary sm:text-xl">
                                    @yield('page-heading', 'Resumen del portal')
                                </h1>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            @yield('portal-header-actions')
                            <a href="{{ route('home') }}" class="hidden min-h-10 items-center gap-2 rounded-lg px-3 text-sm font-semibold text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary sm:inline-flex">
                                <span class="material-symbols-outlined text-lg" aria-hidden="true">open_in_new</span>
                                <span>Ver sitio público</span>
                            </a>
                        </div>
                    </div>
                    <nav aria-label="Secciones del portal" class="flex gap-2 overflow-x-auto border-t border-outline-variant/40 px-4 py-2 md:hidden">
                        @yield('portal-mobile-navigation')
                    </nav>
                </header>

                <main id="contenido" class="flex-1 px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                    <div class="mx-auto w-full max-w-7xl">
                        @yield('breadcrumbs')
                        @yield('content')
                    </div>
                </main>

                <footer class="border-t border-outline-variant/50 px-4 py-4 text-center text-xs text-on-surface-variant sm:px-6 lg:px-8">
                    Portal privado · Patitas Felices
                </footer>
            </div>
        </div>
    </body>
</html>
