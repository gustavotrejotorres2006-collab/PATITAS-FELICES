<?php

namespace Tests\Feature;

use App\Models\Atencion;
use App\Models\Auditoria;
use App\Models\CategoriaProducto;
use App\Models\Cita;
use App\Models\ConfiguracionClinica;
use App\Models\Especialidad;
use App\Models\Mascota;
use App\Models\Notificacion;
use App\Models\PermisoMascota;
use App\Models\Producto;
use App\Models\Profesional;
use App\Models\Propietario;
use App\Models\Role;
use App\Models\Servicio;
use App\Models\User;
use App\Models\Venta;
use Tests\TestCase;

class DatabaseModelsTest extends TestCase
{
    public function test_core_business_models_exist(): void
    {
        $this->assertTrue(class_exists(User::class));
        $this->assertTrue(class_exists(Role::class));
        $this->assertTrue(class_exists(Propietario::class));
        $this->assertTrue(class_exists(Mascota::class));
        $this->assertTrue(class_exists(Profesional::class));
        $this->assertTrue(class_exists(Especialidad::class));
        $this->assertTrue(class_exists(Servicio::class));
        $this->assertTrue(class_exists(Cita::class));
        $this->assertTrue(class_exists(Atencion::class));
        $this->assertTrue(class_exists(CategoriaProducto::class));
        $this->assertTrue(class_exists(Producto::class));
        $this->assertTrue(class_exists(Venta::class));
        $this->assertTrue(class_exists(Notificacion::class));
        $this->assertTrue(class_exists(Auditoria::class));
        $this->assertTrue(class_exists(ConfiguracionClinica::class));
        $this->assertTrue(class_exists(PermisoMascota::class));
    }
}
