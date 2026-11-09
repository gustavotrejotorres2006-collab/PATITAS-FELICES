@extends('layouts.portal')

@section('title', 'Atención clínica')
@section('portal-section', 'Módulo médico')
@section('page-heading', 'Citas pendientes de atención')
@section('meta-description', 'Atiende tus citas y consulta expedientes clínicos veterinarios.')

@section('breadcrumbs')
    <nav aria-label="Ruta de navegación" class="mb-6 text-sm text-on-surface-variant">
        <span aria-current="page" class="font-semibold text-primary">Atención clínica</span>
    </nav>
@endsection

@section('content')
    <section>
        <p class="max-w-3xl leading-7 text-on-surface-variant">Solo se muestran las citas asignadas a tu perfil. Los expedientes y notas clínicas son confidenciales y no se pueden eliminar.</p>
    </section>

    <section aria-label="Citas pendientes de atención" class="mt-6 overflow-hidden rounded-2xl border border-outline-variant/50 bg-surface-container-lowest shadow-sm">
        @if ($citas->isEmpty())
            <div class="grid min-h-48 place-items-center px-6 py-10 text-center">
                <div>
                    <span class="material-symbols-outlined text-4xl text-secondary" aria-hidden="true">event_available</span>
                    <p class="mt-2 font-semibold text-primary">No tienes citas pendientes de atención</p>
                    <p class="mt-1 text-sm text-on-surface-variant">Las nuevas citas aparecerán aquí cuando estén asignadas a tu perfil.</p>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[42rem] border-collapse text-left text-sm">
                    <thead class="bg-surface-container-low text-xs uppercase tracking-wide text-on-surface-variant">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold">Fecha</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Paciente</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Propietario</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Servicios</th>
                            <th scope="col" class="px-5 py-3"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/40">
                        @foreach ($citas as $cita)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <p class="font-semibold text-on-surface">{{ $cita->inicio->format('d/m/Y H:i') }}</p>
                                    @if ($cita->urgente)
                                        <span class="mt-2 inline-flex rounded-full bg-error-container px-2.5 py-1 text-xs font-semibold text-on-error-container">Urgencia</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-on-surface">{{ $cita->mascota->nombre }}</p>
                                    <p class="mt-1 text-xs text-on-surface-variant">{{ ucfirst($cita->mascota->especie) }}</p>
                                </td>
                                <td class="px-5 py-4">{{ $cita->propietario?->nombre ?? 'Sin propietario' }}</td>
                                <td class="px-5 py-4">
                                    @forelse ($cita->servicios as $servicio)
                                        <p>{{ $servicio->nombre }}</p>
                                    @empty
                                        <span class="text-on-surface-variant">Sin servicio indicado</span>
                                    @endforelse
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <a href="{{ route('portal.atenciones.create', $cita) }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-primary-container px-4 py-2 text-sm font-semibold text-on-primary hover:bg-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                                        Atender
                                        <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
                                    </a>
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
