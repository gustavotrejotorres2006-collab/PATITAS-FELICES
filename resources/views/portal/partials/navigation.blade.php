@php
    $mobile = $mobile ?? false;
    $esRecepcionista = strtolower(auth()->user()?->role?->nombre ?? '') === 'recepcionista';
    $esVeterinario = strtolower(auth()->user()?->role?->nombre ?? '') === 'veterinario';
    $esAdministrador = strtolower(auth()->user()?->role?->nombre ?? '') === 'administrador';
    $agenda = ['route' => 'portal.citas.index', 'label' => 'Agenda', 'icon' => 'calendar_month', 'active' => request()->routeIs('portal.citas.*')];
    $expedientes = ['route' => 'portal.atenciones.index', 'label' => 'Atención clínica', 'icon' => 'medical_information', 'active' => request()->routeIs('portal.atenciones.*')];
    $hospitalizaciones = ['route' => 'portal.hospitalizaciones.index', 'label' => 'Quirófano y hospitalización', 'icon' => 'local_hospital', 'active' => request()->routeIs('portal.hospitalizaciones.*', 'portal.cirugias.*', 'portal.espacios.*')];
    $links = match (true) {
        $esRecepcionista => [$agenda],
        $esVeterinario => [$expedientes, $hospitalizaciones],
        default => [
            ['route' => 'portal.dashboard', 'label' => 'Resumen', 'icon' => 'space_dashboard', 'active' => request()->routeIs('portal.dashboard')],
            ...($esAdministrador ? [['route' => 'portal.inventario.index', 'label' => 'Inventario', 'icon' => 'inventory_2', 'active' => request()->routeIs('portal.inventario.*')]] : []),
            ['route' => 'portal.propietarios.index', 'label' => 'Propietarios', 'icon' => 'group', 'active' => request()->routeIs('portal.propietarios.*')],
            ['route' => 'portal.mascotas.index', 'label' => 'Mascotas', 'icon' => 'pets', 'active' => request()->routeIs('portal.mascotas.*', 'portal.propietarios.mascotas.*')],
            ['route' => 'portal.profesionales.index', 'label' => 'Profesionales', 'icon' => 'medical_services', 'active' => request()->routeIs('portal.profesionales.*', 'portal.especialidades.*')],
            $agenda,
            $hospitalizaciones,
        ],
    };
@endphp

@foreach ($links as $link)
    <a
        href="{{ route($link['route']) }}"
        @if ($link['active']) aria-current="page" @endif
        class="{{ $mobile
            ? ($link['active']
                ? 'shrink-0 rounded-full bg-primary-container px-4 py-2 text-sm font-semibold text-on-primary'
                : 'shrink-0 rounded-full bg-surface-container px-4 py-2 text-sm font-semibold text-primary')
            : ($link['active']
                ? 'flex min-h-11 items-center gap-3 rounded-lg bg-primary-container px-3 text-sm font-semibold text-on-primary'
                : 'flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-semibold text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary') }}"
    >
        @unless ($mobile)
            <span class="material-symbols-outlined text-xl" aria-hidden="true">{{ $link['icon'] }}</span>
        @endunless
        {{ $link['label'] }}
    </a>
@endforeach
