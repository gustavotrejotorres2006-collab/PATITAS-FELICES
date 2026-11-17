<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Especialidad;
use App\Models\Profesional;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfesionalController extends Controller
{
    private const DIAS = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo',
    ];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($filters['buscar'] ?? '');

        $profesionales = Profesional::query()
            ->with('especialidades:id,nombre')
            ->withCount('citas')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellido', 'like', "%{$search}%")
                        ->orWhere('cedula_profesional', 'like', "%{$search}%")
                        ->orWhere('telefono', 'like', "%{$search}%");
                });
            })
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->paginate(15)
            ->withQueryString();

        return view('portal.profesionales.index', compact('profesionales', 'search'));
    }

    public function create(): View
    {
        $especialidades = Especialidad::query()->orderBy('nombre')->get();
        $profesional = new Profesional;

        return view('portal.profesionales.create', compact('especialidades', 'profesional'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedProfessionalData($request);
        $especialidades = $data['especialidades'] ?? [];
        $usuarioEmail = $data['usuario_email'] ?? null;
        $usuarioPassword = $data['usuario_password'] ?? null;
        unset($data['especialidades'], $data['usuario_email'], $data['usuario_password']);

        $profesional = DB::transaction(function () use ($data, $especialidades, $usuarioEmail, $usuarioPassword): Profesional {
            $profesional = Profesional::query()->create($data);
            $profesional->especialidades()->sync($especialidades);

            if ($usuarioEmail !== null && $usuarioEmail !== '') {
                $this->syncVeterinarianAccount($profesional, $usuarioEmail, $usuarioPassword);
            }

            return $profesional;
        });

        return redirect()
            ->route('portal.profesionales.edit', $profesional)
            ->with('success', 'El profesional se registró. Ahora puedes configurar su horario semanal.');
    }

    public function show(Profesional $profesional): View
    {
        $profesional->load([
            'especialidades' => fn (BelongsToMany $query) => $query->orderBy('nombre'),
            'horarios' => fn (HasMany $query) => $query->orderBy('dia_semana'),
            'user',
        ]);

        return view('portal.profesionales.show', [
            'profesional' => $profesional,
            'dias' => self::DIAS,
        ]);
    }

    public function edit(Profesional $profesional): View
    {
        $profesional->load(['especialidades:id', 'horarios', 'user']);
        $especialidades = Especialidad::query()->orderBy('nombre')->get();
        $horarios = $profesional->horarios->keyBy('dia_semana');

        return view('portal.profesionales.edit', [
            'profesional' => $profesional,
            'especialidades' => $especialidades,
            'dias' => self::DIAS,
            'horarios' => $horarios,
        ]);
    }

    public function update(Request $request, Profesional $profesional): RedirectResponse
    {
        $data = $this->validatedProfessionalData($request, $profesional, true);
        $especialidades = $data['especialidades'] ?? [];
        $horarios = $data['horarios'];
        $usuarioEmail = $data['usuario_email'] ?? null;
        $usuarioPassword = $data['usuario_password'] ?? null;
        unset($data['especialidades'], $data['horarios'], $data['usuario_email'], $data['usuario_password']);

        DB::transaction(function () use ($profesional, $data, $especialidades, $horarios, $usuarioEmail, $usuarioPassword): void {
            $profesional->update($data);
            $profesional->especialidades()->sync($especialidades);

            if ($usuarioEmail !== null && $usuarioEmail !== '') {
                $this->syncVeterinarianAccount($profesional, $usuarioEmail, $usuarioPassword);
            }

            $profesional->horarios()->delete();

            foreach ($horarios as $dia => $horario) {
                if (! $horario['activo']) {
                    continue;
                }

                $profesional->horarios()->create([
                    'dia_semana' => $dia,
                    'hora_inicio' => $horario['hora_inicio'],
                    'hora_fin' => $horario['hora_fin'],
                    'activo' => true,
                ]);
            }
        });

        return redirect()
            ->route('portal.profesionales.show', $profesional)
            ->with('success', 'El perfil y el horario semanal se actualizaron correctamente.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedProfessionalData(
        Request $request,
        ?Profesional $profesional = null,
        bool $withSchedule = false,
    ): array {
        $rules = [
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['nullable', 'string', 'max:255'],
            'cedula_profesional' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('profesionales', 'cedula_profesional')->ignore($profesional),
            ],
            'telefono' => ['nullable', 'string', 'max:50'],
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
            'especialidades' => ['nullable', 'array'],
            'especialidades.*' => ['integer', 'distinct', Rule::exists('especialidades', 'id')],
            'usuario_email' => [
                $profesional?->user_id ? 'required' : 'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($profesional?->user_id),
            ],
            'usuario_password' => $profesional?->user_id
                ? ['nullable', 'string', 'min:8']
                : ['required_with:usuario_email', 'string', 'min:8'],
        ];

        if ($withSchedule) {
            $rules['horarios'] = ['required', 'array', 'size:7'];

            foreach (self::DIAS as $dia => $nombre) {
                $rules["horarios.{$dia}.activo"] = ['required', 'boolean'];
                $rules["horarios.{$dia}.hora_inicio"] = ['nullable', 'date_format:H:i'];
                $rules["horarios.{$dia}.hora_fin"] = ['nullable', 'date_format:H:i'];
            }
        }

        $data = $request->validate($rules, [
            'nombre.required' => 'El nombre del profesional es obligatorio.',
            'cedula_profesional.unique' => 'Ya existe un profesional con esa cédula.',
            'especialidades.*.exists' => 'Selecciona especialidades registradas.',
        ]);

        if ($withSchedule) {
            foreach (self::DIAS as $dia => $nombre) {
                $horario = $data['horarios'][$dia] ?? null;

                if (! $horario || ! $horario['activo']) {
                    continue;
                }

                if (empty($horario['hora_inicio']) || empty($horario['hora_fin'])) {
                    throw ValidationException::withMessages([
                        "horarios.{$dia}.hora_inicio" => "Indica la hora de inicio y fin para {$nombre}.",
                    ]);
                }

                if ($horario['hora_fin'] <= $horario['hora_inicio']) {
                    throw ValidationException::withMessages([
                        "horarios.{$dia}.hora_fin" => "La hora de fin de {$nombre} debe ser posterior al inicio.",
                    ]);
                }
            }
        }

        return $data;
    }

    private function syncVeterinarianAccount(
        Profesional $profesional,
        string $email,
        ?string $password,
    ): void {
        $user = $profesional->user;

        if ($user instanceof User) {
            $user->name = trim($profesional->nombre.' '.$profesional->apellido);
            $user->email = $email;

            if ($password !== null && $password !== '') {
                $user->password = $password;
            }

            $user->save();

            return;
        }

        $role = Role::query()->firstOrCreate(
            ['nombre' => 'Veterinario'],
            [
                'descripcion' => 'Acceso al módulo médico y a expedientes clínicos.',
                'activo' => true,
            ],
        );

        if (! $role->activo) {
            throw ValidationException::withMessages([
                'usuario_email' => 'El rol Veterinario está inactivo. Actívalo antes de crear cuentas clínicas.',
            ]);
        }

        $user = User::query()->create([
            'name' => trim($profesional->nombre.' '.$profesional->apellido),
            'email' => $email,
            'password' => $password,
            'role_id' => $role->id,
            'estado' => 'activo',
        ]);

        $profesional->user()->associate($user);
        $profesional->save();
    }
}
