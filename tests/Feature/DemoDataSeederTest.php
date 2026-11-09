<?php

namespace Tests\Feature;

use App\Models\Atencion;
use App\Models\Cirugia;
use App\Models\EspacioClinico;
use App\Models\Hospitalizacion;
use App\Models\LoteInventario;
use App\Models\Mascota;
use App\Models\MonitoreoHospitalario;
use App\Models\MovimientoInventario;
use App\Models\Propietario;
use App\Models\Role;
use App\Models\User;
use App\Models\Vacunacion;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_is_repeatable_and_preserves_existing_records(): void
    {
        $existingOwner = Propietario::query()->create([
            'codigo' => 'CLIENTE-EXISTENTE',
            'nombre' => 'Cliente',
            'apellido' => 'Existente',
            'estado' => 'activo',
        ]);

        $this->seed(DatabaseSeeder::class);

        $firstCounts = [
            'roles' => Role::query()->count(),
            'users' => User::query()->count(),
            'propietarios' => Propietario::query()->count(),
            'mascotas' => Mascota::query()->count(),
            'relaciones_mascota_propietario' => DB::table('mascota_propietario')->count(),
            'servicios_cita' => DB::table('cita_servicios')->count(),
            'atenciones' => Atencion::query()->count(),
            'notas_clinicas' => DB::table('notas_clinicas')->count(),
            'lotes_inventario' => LoteInventario::query()->count(),
            'movimientos_inventario' => MovimientoInventario::query()->count(),
            'vacunaciones' => Vacunacion::query()->count(),
            'espacios_clinicos' => EspacioClinico::query()->count(),
            'cirugias' => Cirugia::query()->count(),
            'hospitalizaciones' => Hospitalizacion::query()->count(),
            'monitoreos_hospitalarios' => MonitoreoHospitalario::query()->count(),
        ];

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($firstCounts, [
            'roles' => Role::query()->count(),
            'users' => User::query()->count(),
            'propietarios' => Propietario::query()->count(),
            'mascotas' => Mascota::query()->count(),
            'relaciones_mascota_propietario' => DB::table('mascota_propietario')->count(),
            'servicios_cita' => DB::table('cita_servicios')->count(),
            'atenciones' => Atencion::query()->count(),
            'notas_clinicas' => DB::table('notas_clinicas')->count(),
            'lotes_inventario' => LoteInventario::query()->count(),
            'movimientos_inventario' => MovimientoInventario::query()->count(),
            'vacunaciones' => Vacunacion::query()->count(),
            'espacios_clinicos' => EspacioClinico::query()->count(),
            'cirugias' => Cirugia::query()->count(),
            'hospitalizaciones' => Hospitalizacion::query()->count(),
            'monitoreos_hospitalarios' => MonitoreoHospitalario::query()->count(),
        ]);
        $this->assertModelExists($existingOwner);
        $this->assertDatabaseHas('users', [
            'email' => 'admin@patitas.test',
            'estado' => 'activo',
        ]);
        $this->assertDatabaseHas('roles', [
            'nombre' => 'Veterinario',
            'activo' => true,
        ]);
        $this->assertDatabaseHas('citas', [
            'folio' => 'DEMO-CIT-003',
            'estado' => 'pendiente',
        ]);
        $this->assertDatabaseHas('cirugias', [
            'folio' => 'DEMO-CIR-001',
            'estado' => 'programada',
        ]);
        $this->assertDatabaseHas('hospitalizaciones', [
            'folio' => 'DEMO-HOSP-002',
            'estado' => 'internada',
        ]);

        $this->post(route('login.store'), [
            'email' => 'admin@patitas.test',
            'password' => 'PatitasDemo2026!',
        ])->assertRedirect(route('portal.dashboard'));
    }
}
