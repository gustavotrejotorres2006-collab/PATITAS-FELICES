<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Atencion;
use App\Models\CatalogoVacuna;
use App\Models\Cita;
use App\Models\Profesional;
use App\Services\ClinicaService;
use App\Services\InventarioService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AtencionController extends Controller
{
    public function index(Request $request): View
    {
        $profesional = $this->authenticatedProfessional($request);
        $citas = Cita::query()
            ->where('profesional_id', $profesional->id)
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->whereDoesntHave('atenciones')
            ->with(['mascota:id,nombre,especie', 'propietario:id,nombre', 'servicios:id,nombre'])
            ->orderBy('inicio')
            ->orderBy('id')
            ->paginate(15);

        return view('portal.atenciones.index', compact('citas'));
    }

    public function create(Request $request, Cita $cita): View
    {
        $profesional = $this->authenticatedProfessional($request);
        $this->ensureCitaCanBeAttended($cita, $profesional);

        $cita->load(['mascota:id,nombre,especie', 'propietario:id,nombre', 'servicios:id,nombre']);
        $vacunas = CatalogoVacuna::query()
            ->where('activo', true)
            ->whereHas('producto', fn (Builder $query) => $query
                ->where('estado', 'activo')
                ->whereHas('lotes', fn (Builder $query) => $query
                    ->where('estado', 'disponible')
                    ->where('cantidad_disponible', '>', 0)
                    ->where(fn (Builder $query) => $query
                        ->whereNull('fecha_caducidad')
                        ->orWhereDate('fecha_caducidad', '>=', today()))))
            ->with('producto')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'especie', 'producto_id']);

        return view('portal.atenciones.create', compact('cita', 'vacunas'));
    }

    public function store(
        Request $request,
        Cita $cita,
        ClinicaService $clinicaService,
        InventarioService $inventarioService,
    ): RedirectResponse {
        $profesional = $this->authenticatedProfessional($request);
        $this->ensureCitaCanBeAttended($cita, $profesional);

        $data = $request->validate([
            'fecha_atencion' => ['required', 'date_format:Y-m-d\TH:i', 'before_or_equal:now'],
            'motivo' => ['nullable', 'string', 'max:2000'],
            'diagnostico' => ['required', 'string', 'max:1000'],
            'nota' => ['required', 'string', 'max:10000'],
            'peso_kg' => ['nullable', 'numeric', 'between:0.01,1000'],
            'temperatura_c' => ['nullable', 'numeric', 'between:20,50'],
            'frecuencia_cardiaca' => ['nullable', 'integer', 'between:1,500'],
            'frecuencia_respiratoria' => ['nullable', 'integer', 'between:1,300'],
            'presion_arterial' => ['nullable', 'string', 'max:50'],
            'condicion_corporal' => ['nullable', 'numeric', 'between:1,9'],
            'observaciones_vitales' => ['nullable', 'string', 'max:2000'],
            'hospitalizacion_recomendada' => ['nullable', 'boolean'],
            'vacunas_aplicadas' => ['nullable', 'array'],
            'vacunas_aplicadas.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('catalogo_vacunas', 'id')
                    ->where('activo', true)
                    ->whereNotNull('producto_id'),
            ],
        ], [
            'diagnostico.required' => 'Registra al menos un diagnóstico antes de guardar la atención.',
            'nota.required' => 'La nota clínica es obligatoria.',
            'fecha_atencion.before_or_equal' => 'La fecha de atención no puede ser futura.',
        ]);

        $vacunas = CatalogoVacuna::query()
            ->whereIn('id', $data['vacunas_aplicadas'] ?? [])
            ->where('activo', true)
            ->get();

        $atencion = $clinicaService->recordAppointment(
            $cita,
            $profesional,
            $request->user(),
            $data,
            $vacunas,
            $inventarioService,
        );

        return redirect()
            ->route('portal.atenciones.show', $atencion)
            ->with('success', 'La atención y su expediente clínico quedaron registrados.');
    }

    public function show(Request $request, Atencion $atencion): View
    {
        $profesional = $this->authenticatedProfessional($request);
        abort_unless($atencion->profesional_id === $profesional->id, 404);

        $atencion->load([
            'mascota',
            'propietario',
            'cita',
            'signosVitales',
            'notas' => fn (HasMany $query) => $query
                ->with(['autor:id,name', 'profesional:id,nombre,apellido'])
                ->orderBy('version'),
            'diagnosticos.diagnostico',
            'vacunaciones.vacuna',
        ]);

        $historial = $atencion->mascota->atenciones()
            ->whereKeyNot($atencion->id)
            ->with(['notas' => fn (HasMany $query) => $query->orderByDesc('version')->limit(1)])
            ->orderByDesc('fecha_atencion')
            ->get();

        return view('portal.atenciones.show', compact('atencion', 'historial'));
    }

    public function storeNote(Request $request, Atencion $atencion, ClinicaService $clinicaService): RedirectResponse
    {
        $profesional = $this->authenticatedProfessional($request);
        abort_unless($atencion->profesional_id === $profesional->id, 404);

        $data = $request->validate([
            'contenido' => ['required', 'string', 'max:10000'],
            'hospitalizacion_recomendada' => ['nullable', 'boolean'],
        ], [
            'contenido.required' => 'La nota clínica es obligatoria.',
        ]);

        $clinicaService->appendNote($atencion, $profesional, $request->user(), $data);

        return redirect()
            ->route('portal.atenciones.show', $atencion)
            ->with('success', 'Se agregó una nueva versión de la nota clínica.');
    }

    private function authenticatedProfessional(Request $request): Profesional
    {
        $profesional = $request->user()->profesional;

        abort_unless($profesional instanceof Profesional && $profesional->estado === 'activo', 403);

        return $profesional;
    }

    private function ensureCitaCanBeAttended(Cita $cita, Profesional $profesional): void
    {
        abort_unless(
            $cita->profesional_id === $profesional->id
                && in_array($cita->estado, ['pendiente', 'confirmada'], true)
                && ! $cita->atenciones()->exists(),
            404,
        );
    }
}
