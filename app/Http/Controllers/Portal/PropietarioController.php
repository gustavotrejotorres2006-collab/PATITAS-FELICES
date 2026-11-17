<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Propietario;
use App\Services\PropietarioService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropietarioController extends Controller
{
    public function __construct(private PropietarioService $propietarioService) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($filters['buscar'] ?? '');

        $propietarios = Propietario::query()
            ->withCount([
                'mascotas' => fn (Builder $query) => $query->where('mascota_propietario.estado', 'activo'),
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('codigo', 'like', "%{$search}%")
                        ->orWhere('nombre', 'like', "%{$search}%")
                        ->orWhere('apellido', 'like', "%{$search}%")
                        ->orWhere('telefono', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->paginate(15)
            ->withQueryString();

        return view('portal.propietarios.index', compact('propietarios', 'search'));
    }

    public function create(): View
    {
        return view('portal.propietarios.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $propietario = $this->propietarioService->create($this->validatedOwnerData($request));

        return redirect()
            ->route('portal.propietarios.show', $propietario)
            ->with('success', 'El propietario se registró correctamente.');
    }

    public function show(Propietario $propietario): View
    {
        $propietario->load([
            'mascotas' => fn ($query) => $query
                ->wherePivot('estado', 'activo')
                ->orderBy('mascotas.nombre'),
        ]);

        return view('portal.propietarios.show', compact('propietario'));
    }

    public function edit(Propietario $propietario): View
    {
        return view('portal.propietarios.edit', compact('propietario'));
    }

    public function update(Request $request, Propietario $propietario): RedirectResponse
    {
        $this->propietarioService->update($propietario, $this->validatedOwnerData($request));

        return redirect()
            ->route('portal.propietarios.show', $propietario)
            ->with('success', 'Los datos del propietario se actualizaron correctamente.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedOwnerData(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:2000'],
        ], [
            'nombre.required' => 'El nombre del propietario es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
        ]);
    }
}
