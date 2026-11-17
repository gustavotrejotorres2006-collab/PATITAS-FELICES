<?php

namespace Tests\Feature;

use App\Services\AuthService;
use App\Services\CajaService;
use App\Services\CitaService;
use App\Services\ClinicaService;
use App\Services\CompraService;
use App\Services\InventarioService;
use App\Services\MascotaService;
use App\Services\NotificacionService;
use App\Services\ProfesionalService;
use App\Services\PropietarioService;
use App\Services\RoleService;
use App\Services\UserService;
use App\Services\VentaService;
use Tests\TestCase;

class DatabaseServicesTest extends TestCase
{
    public function test_business_services_exist(): void
    {
        $this->assertTrue(class_exists(AuthService::class));
        $this->assertTrue(class_exists(UserService::class));
        $this->assertTrue(class_exists(RoleService::class));
        $this->assertTrue(class_exists(MascotaService::class));
        $this->assertTrue(class_exists(PropietarioService::class));
        $this->assertTrue(class_exists(ProfesionalService::class));
        $this->assertTrue(class_exists(CitaService::class));
        $this->assertTrue(class_exists(ClinicaService::class));
        $this->assertTrue(class_exists(InventarioService::class));
        $this->assertTrue(class_exists(CompraService::class));
        $this->assertTrue(class_exists(CajaService::class));
        $this->assertTrue(class_exists(VentaService::class));
        $this->assertTrue(class_exists(NotificacionService::class));
    }
}
