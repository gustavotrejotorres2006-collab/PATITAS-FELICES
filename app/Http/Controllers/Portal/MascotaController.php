<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Mascota;
use App\Models\Propietario;
use App\Services\MascotaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MascotaController extends Controller
{
    private const ESPECIES = ['perro', 'gato', 'ave', 'conejo', 'reptil', 'otro'];

    public function __construct(private MascotaService $mascotaService) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($filters['buscar'] ?? '');

        $mascotas = Mascota::query()
            ->with([
                'propietarios' => fn (BelongsToMany $query) => $query->wherePivot('estado', 'activo'),
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('codigo', 'like', "%{$search}%")
                        ->orWhere('nombre', 'like', "%{$search}%")
                        ->orWhere('especie', 'like', "%{$search}%")
                        ->orWhere('microchip', 'like', "%{$search}%")
                        ->orWhereHas('propietarios', function (Builder $query) use ($search): void {
                            $query->where('mascota_propietario.estado', 'activo')
                                ->where(function (Builder $query) use ($search): void {
                                    $query->where('propietarios.nombre', 'like', "%{$search}%")
                                        ->orWhere('propietarios.apellido', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('portal.mascotas.index', compact('mascotas', 'search'));
    }

    public function create(Propietario $propietario): View
    {
        abort_if($propietario->estado !== 'activo', 404);

        return view('portal.mascotas.create', compact('propietario'));
    }

    public function store(Request $request, Propietario $propietario): RedirectResponse
    {
        abort_if($propietario->estado !== 'activo', 404);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'especie' => ['required', 'string', Rule::in(self::ESPECIES)],
            'raza' => ['nullable', 'string', 'max:255'],
            'sexo' => ['nullable', Rule::in(['macho', 'hembra', 'no_especificado'])],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'color' => ['nullable', 'string', 'max:255'],
            'microchip' => ['nullable', 'string', 'max:255', Rule::unique('mascotas', 'microchip')],
            'esterilizado' => ['sometimes', 'boolean'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
        ], [
            'nombre.required' => 'El nombre de la mascota es obligatorio.',
            'especie.in' => 'Selecciona una especie válida.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'microchip.unique' => 'Ya existe una mascota registrada con ese microchip.',
        ]);

        $data['esterilizado'] = $request->boolean('esterilizado');

        DB::transaction(function () use ($data, $propietario): void {
            $mascota = $this->mascotaService->create($data);
            $this->mascotaService->assignOwner($mascota, $propietario, true);
        });

        return redirect()
            ->route('portal.propietarios.show', $propietario)
            ->with('success', 'La mascota se registró y quedó asociada al propietario.');
    }
}
