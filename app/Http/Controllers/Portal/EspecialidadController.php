<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Especialidad;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EspecialidadController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($filters['buscar'] ?? '');

        $especialidades = Especialidad::query()
            ->withCount('profesionales')
            ->when($search !== '', fn (Builder $query) => $query->where('nombre', 'like', "%{$search}%"))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('portal.especialidades.index', compact('especialidades', 'search'));
    }

    public function create(): View
    {
        return view('portal.especialidades.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Especialidad::query()->create($this->validatedSpecialtyData($request));

        return redirect()
            ->route('portal.especialidades.index')
            ->with('success', 'La especialidad se registró correctamente.');
    }

    public function edit(Especialidad $especialidad): View
    {
        return view('portal.especialidades.edit', compact('especialidad'));
    }

    public function update(Request $request, Especialidad $especialidad): RedirectResponse
    {
        $especialidad->update($this->validatedSpecialtyData($request, $especialidad));

        return redirect()
            ->route('portal.especialidades.index')
            ->with('success', 'La especialidad se actualizó correctamente.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedSpecialtyData(Request $request, ?Especialidad $especialidad = null): array
    {
        return $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('especialidades', 'nombre')->ignore($especialidad),
            ],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'activo' => ['sometimes', 'boolean'],
        ], [
            'nombre.required' => 'El nombre de la especialidad es obligatorio.',
            'nombre.unique' => 'Ya existe una especialidad con ese nombre.',
        ]) + ['activo' => $request->boolean('activo', $especialidad?->activo ?? true)];
    }
}
