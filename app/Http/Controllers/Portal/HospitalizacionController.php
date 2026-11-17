<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Atencion;
use App\Models\Cirugia;
use App\Models\EspacioClinico;
use App\Models\Hospitalizacion;
use App\Models\Producto;
use App\Models\Profesional;
use App\Models\User;
use App\Services\HospitalizacionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HospitalizacionController extends Controller
{
    public function index(Request $request): View
    {
        $professional = $this->authenticatedProfessional($request);
        $attentionQuery = Atencion::query()
            ->where('estado', 'abierta')
            ->whereNotNull('propietario_id')
            ->whereHas('notas')
            ->when(
                $professional,
                fn (Builder $query) => $query->where('profesional_id', $professional->id),
            );
        $attentionRelations = ['mascota:id,nombre,especie', 'propietario:id,nombre', 'profesional:id,nombre,apellido'];
        $admissionAttentions = (clone $attentionQuery)
            ->where('hospitalizacion_recomendada', true)
            ->whereDoesntHave('hospitalizaciones', fn (Builder $query) => $query->where('estado', 'internada'))
            ->with($attentionRelations)
            ->orderByDesc('fecha_atencion')
            ->get();
        $surgeryAttentions = (clone $attentionQuery)
            ->whereDoesntHave('cirugias', fn (Builder $query) => $query->where('estado', 'programada'))
            ->with($attentionRelations)
            ->orderByDesc('fecha_atencion')
            ->get();
        $hospitalizaciones = Hospitalizacion::query()
            ->where('estado', 'internada')
            ->when(
                $professional,
                fn (Builder $query) => $query->whereHas(
                    'atencion',
                    fn (Builder $attentionQuery) => $attentionQuery->where('profesional_id', $professional->id),
                ),
            )
            ->with([
                'atencion.mascota:id,nombre,especie',
                'atencion.propietario:id,nombre',
                'espacio:id,nombre',
                'ultimoMonitoreo',
                'monitoreos' => fn (HasMany $query) => $query
                    ->orderByDesc('registrada_at')
                    ->limit(3),
            ])
            ->orderBy('ingresada_at')
            ->get();
        $cirugias = Cirugia::query()
            ->where('estado', 'programada')
            ->when(
                $professional,
                fn (Builder $query) => $query->where(function (Builder $surgeries) use ($professional): void {
                    $surgeries->whereHas(
                        'atencion',
                        fn (Builder $attentionQuery) => $attentionQuery->where('profesional_id', $professional->id),
                    )
                        ->orWhere('cirujano_id', $professional->id)
                        ->orWhere('anestesiologo_id', $professional->id);
                }),
            )
            ->with([
                'atencion.mascota:id,nombre,especie',
                'espacio:id,nombre',
                'cirujano:id,nombre,apellido',
                'anestesiologo:id,nombre,apellido',
            ])
            ->orderBy('programada_at')
            ->get();
        $espacios = EspacioClinico::query()
            ->where('activo', true)
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->get();
        $profesionales = Profesional::query()
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get(['id', 'nombre', 'apellido']);
        $productos = Producto::query()
            ->where('estado', 'activo')
            ->whereHas('lotes', fn (Builder $query) => $query
                ->where('estado', 'disponible')
                ->where('cantidad_disponible', '>', 0)
                ->where(fn (Builder $query) => $query
                    ->whereNull('fecha_caducidad')
                    ->orWhereDate('fecha_caducidad', '>=', today())))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'unidad_base']);
        $espaciosDisponibles = $espacios->where('estado', 'disponible');
        $hospitalizacionesVencidas = $hospitalizaciones
            ->filter(fn (Hospitalizacion $hospitalization): bool => $hospitalization->monitoreoVencido())
            ->values();
        $esAdministrador = $this->isAdministrator($request->user());

        return view('portal.hospitalizaciones.index', compact(
            'admissionAttentions',
            'cirugias',
            'esAdministrador',
            'espacios',
            'espaciosDisponibles',
            'hospitalizaciones',
            'hospitalizacionesVencidas',
            'productos',
            'profesionales',
            'surgeryAttentions',
        ));
    }

    public function storeSpace(Request $request, HospitalizacionService $service): RedirectResponse
    {
        abort_unless($this->isAdministrator($request->user()), 403);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150', Rule::unique('espacios_clinicos', 'nombre')],
            'tipo' => ['required', Rule::in(['quirofano', 'hospitalizacion'])],
        ]);
        $service->createSpace($data, $request->user());

        return redirect()
            ->route('portal.hospitalizaciones.index')
            ->with('success', 'El espacio clínico quedó registrado.');
    }

    public function storeSurgery(Request $request, HospitalizacionService $service): RedirectResponse
    {
        $data = $request->validate([
            'atencion_id' => ['required', 'integer', Rule::exists('atenciones', 'id')],
            'espacio_id' => [
                'required',
                'integer',
                Rule::exists('espacios_clinicos', 'id')
                    ->where('tipo', 'quirofano')
                    ->where('estado', 'disponible')
                    ->where('activo', true),
            ],
            'cirujano_id' => [
                'required',
                'integer',
                Rule::exists('profesionales', 'id')->where('estado', 'activo'),
            ],
            'anestesiologo_id' => [
                'required',
                'integer',
                'different:cirujano_id',
                Rule::exists('profesionales', 'id')->where('estado', 'activo'),
            ],
            'propietario_id' => ['nullable', 'integer', Rule::exists('propietarios', 'id')],
            'programada_at' => ['required', 'date_format:Y-m-d\TH:i', 'after_or_equal:now'],
            'duracion_minutos' => ['required', 'integer', 'between:15,720'],
            'consentimiento_firmado' => ['required', 'accepted'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ], [
            'programada_at.after_or_equal' => 'La fecha de cirugía debe ser actual o futura.',
            'consentimiento_firmado.accepted' => 'Confirma que el propietario firmó el consentimiento antes de programar la cirugía.',
        ]);
        $attention = Atencion::query()->findOrFail($data['atencion_id']);
        $this->ensureAtencionAccess($request, $attention);
        $service->scheduleSurgery($data, $request->user());

        return redirect()
            ->route('portal.hospitalizaciones.index')
            ->with('success', 'La cirugía quedó programada y se bloquearon los horarios correspondientes.');
    }

    public function completeSurgery(
        Request $request,
        Cirugia $cirugia,
        HospitalizacionService $service,
    ): RedirectResponse {
        $this->ensureSurgeryAccess($request, $cirugia);
        $data = $request->validate([
            'insumos' => ['nullable', 'array', 'max:20'],
            'insumos.*.producto_id' => [
                'nullable',
                'integer',
                'distinct',
                Rule::exists('productos', 'id')->where('estado', 'activo'),
            ],
            'insumos.*.cantidad' => ['nullable', 'numeric', 'gt:0', 'max:1000000'],
        ]);
        $supplies = [];

        foreach ($data['insumos'] ?? [] as $index => $supply) {
            $hasProduct = isset($supply['producto_id']) && $supply['producto_id'] !== '';
            $hasQuantity = isset($supply['cantidad']) && $supply['cantidad'] !== '';

            if ($hasProduct !== $hasQuantity) {
                throw ValidationException::withMessages([
                    "insumos.{$index}.".($hasProduct ? 'cantidad' : 'producto_id') => 'Selecciona un producto e indica su cantidad utilizada.',
                ]);
            }

            if ($hasProduct) {
                $supplies[] = $supply;
            }
        }

        $service->completeSurgery($cirugia, $supplies, $request->user());

        return redirect()
            ->route('portal.hospitalizaciones.index')
            ->with('success', 'La cirugía quedó cerrada, los insumos se descontaron por FIFO y el quirófano requiere desinfección.');
    }

    public function storeHospitalization(Request $request, HospitalizacionService $service): RedirectResponse
    {
        $data = $request->validate([
            'atencion_id' => ['required', 'integer', Rule::exists('atenciones', 'id')],
            'espacio_id' => [
                'required',
                'integer',
                Rule::exists('espacios_clinicos', 'id')
                    ->where('tipo', 'hospitalizacion')
                    ->where('estado', 'disponible')
                    ->where('activo', true),
            ],
        ]);
        $attention = Atencion::query()->findOrFail($data['atencion_id']);
        $this->ensureAtencionAccess($request, $attention);
        $space = EspacioClinico::query()->findOrFail($data['espacio_id']);
        $service->admit($attention, $space, $request->user());

        return redirect()
            ->route('portal.hospitalizaciones.index')
            ->with('success', 'La mascota ingresó al espacio de hospitalización.');
    }

    public function storeMonitoring(
        Request $request,
        Hospitalizacion $hospitalizacion,
        HospitalizacionService $service,
    ): RedirectResponse {
        abort_unless($this->authenticatedProfessional($request) instanceof Profesional, 403);
        $this->ensureAtencionAccess($request, $hospitalizacion->atencion);
        $data = $request->validate([
            'temperatura_c' => ['required', 'numeric', 'between:20,50'],
            'frecuencia_cardiaca' => ['nullable', 'integer', 'between:1,500'],
            'frecuencia_respiratoria' => ['nullable', 'integer', 'between:1,300'],
            'tratamiento' => ['required', 'string', 'max:5000'],
            'nota_progreso' => ['required', 'string', 'max:10000'],
        ]);
        $service->recordMonitoring($hospitalizacion, $data, $request->user());

        return redirect()
            ->route('portal.hospitalizaciones.index')
            ->with('success', 'Se registró el monitoreo y se agregó la nota al expediente clínico.');
    }

    public function discharge(
        Request $request,
        Hospitalizacion $hospitalizacion,
        HospitalizacionService $service,
    ): RedirectResponse {
        $this->ensureAtencionAccess($request, $hospitalizacion->atencion);
        $service->discharge($hospitalizacion, $request->user());

        return redirect()
            ->route('portal.hospitalizaciones.index')
            ->with('success', 'Se registró el alta y el espacio quedó pendiente de desinfección.');
    }

    public function disinfect(
        Request $request,
        EspacioClinico $espacio,
        HospitalizacionService $service,
    ): RedirectResponse {
        $service->disinfect($espacio, $request->user());

        return redirect()
            ->route('portal.hospitalizaciones.index')
            ->with('success', 'La desinfección quedó registrada y el espacio está disponible.');
    }

    private function authenticatedProfessional(Request $request): ?Profesional
    {
        if ($this->isAdministrator($request->user())) {
            return null;
        }

        $professional = $request->user()->profesional;
        abort_unless($professional instanceof Profesional && $professional->estado === 'activo', 403);

        return $professional;
    }

    private function ensureAtencionAccess(Request $request, Atencion $attention): void
    {
        $professional = $this->authenticatedProfessional($request);

        abort_if(
            $professional && $attention->profesional_id !== $professional->id,
            404,
        );
    }

    private function ensureSurgeryAccess(Request $request, Cirugia $surgery): void
    {
        $professional = $this->authenticatedProfessional($request);

        abort_if(
            $professional
                && ! in_array($professional->id, [
                    $surgery->atencion->profesional_id,
                    $surgery->cirujano_id,
                    $surgery->anestesiologo_id,
                ], true),
            404,
        );
    }

    private function isAdministrator(User $user): bool
    {
        return strtolower($user->role?->nombre ?? '') === 'administrador';
    }
}
