<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Mascota;
use App\Models\Profesional;
use App\Models\Propietario;
use App\Models\Servicio;
use App\Services\CitaService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CitaController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'fecha' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $citas = Cita::query()
            ->with([
                'mascota:id,nombre',
                'propietario:id,nombre',
                'profesional:id,nombre,apellido',
                'servicios:id,nombre,categoria_id',
                'servicios.categoria:id,nombre',
            ])
            ->when(
                isset($filters['fecha']),
                fn (Builder $query) => $query->whereDate('inicio', $filters['fecha']),
            )
            ->orderBy('inicio')
            ->orderByDesc('urgente')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('portal.citas.index', [
            'citas' => $citas,
            'fecha' => $filters['fecha'] ?? '',
        ]);
    }

    public function create(): View
    {
        $mascotas = Mascota::query()
            ->where('estado', 'activo')
            ->whereHas('propietarios', function (Builder $query): void {
                $query->where('propietarios.estado', 'activo')
                    ->wherePivot('estado', 'activo');
            })
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'especie']);

        $propietarios = Propietario::query()
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $profesionales = Profesional::query()
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get(['id', 'nombre', 'apellido']);

        $servicios = Servicio::query()
            ->where('activo', true)
            ->with('categoria:id,nombre')
            ->orderBy('nombre')
            ->get()
            ->sortBy(fn (Servicio $servicio): string => sprintf(
                '%d-%s',
                $this->servicePriority($servicio),
                Str::lower($servicio->nombre),
            ))
            ->values();

        return view('portal.citas.create', compact(
            'mascotas',
            'propietarios',
            'profesionales',
            'servicios',
        ));
    }

    public function store(Request $request, CitaService $citaService): RedirectResponse
    {
        $data = $request->validate([
            'propietario_id' => [
                'required',
                'integer',
                Rule::exists('propietarios', 'id')->where('estado', 'activo'),
            ],
            'mascota_id' => [
                'required',
                'integer',
                Rule::exists('mascotas', 'id')
                    ->where('estado', 'activo')
                    ->whereNull('deleted_at'),
            ],
            'profesional_id' => [
                'required',
                'integer',
                Rule::exists('profesionales', 'id')->where('estado', 'activo'),
            ],
            'servicios' => ['required', 'array', 'min:1'],
            'servicios.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('servicios', 'id')
                    ->where('activo', true)
                    ->whereNull('deleted_at'),
            ],
            'inicio' => ['required', 'date_format:Y-m-d\TH:i', 'after_or_equal:now'],
            'urgente' => ['nullable', 'boolean'],
            'motivo' => ['nullable', 'string', 'max:2000'],
        ], [
            'inicio.after_or_equal' => 'La fecha y hora de la cita deben ser actuales o futuras.',
            'servicios.required' => 'Selecciona al menos un servicio.',
            'servicios.min' => 'Selecciona al menos un servicio.',
        ]);

        $propietario = Propietario::query()->findOrFail($data['propietario_id']);
        $mascota = Mascota::query()->findOrFail($data['mascota_id']);

        if (! $mascota->propietarios()
            ->where('propietarios.id', $propietario->id)
            ->wherePivot('estado', 'activo')
            ->exists()) {
            throw ValidationException::withMessages([
                'propietario_id' => 'El propietario seleccionado no está vinculado a esta mascota.',
            ]);
        }

        $profesional = Profesional::query()->findOrFail($data['profesional_id']);
        $servicios = Servicio::query()
            ->whereKey($data['servicios'])
            ->with('categoria')
            ->get();

        if ($servicios->count() !== count($data['servicios'])) {
            throw ValidationException::withMessages([
                'servicios' => 'Uno o más servicios ya no están disponibles.',
            ]);
        }

        $citaService->schedule(
            $mascota,
            $propietario,
            $profesional,
            $servicios,
            CarbonImmutable::createFromFormat('Y-m-d\TH:i', $data['inicio']),
            (bool) ($data['urgente'] ?? false),
            $data['motivo'] ?? null,
            (int) $request->user()->getKey(),
        );

        return redirect()
            ->route('portal.citas.index')
            ->with('success', 'La cita se agregó a la agenda.');
    }

    public function cancel(Cita $cita, CitaService $citaService): RedirectResponse
    {
        $citaService->cancel($cita);

        return redirect()
            ->route('portal.citas.index')
            ->with('success', 'La cita se canceló y el horario quedó disponible.');
    }

    private function servicePriority(Servicio $servicio): int
    {
        $categoria = Str::ascii(Str::lower($servicio->categoria?->nombre ?? ''));

        return match (true) {
            Str::contains($categoria, 'cirug') => 1,
            Str::contains($categoria, 'revision') => 2,
            Str::contains($categoria, 'limpieza dental') => 3,
            Str::contains($categoria, ['corte', 'bano']) => 4,
            default => 5,
        };
    }
}
