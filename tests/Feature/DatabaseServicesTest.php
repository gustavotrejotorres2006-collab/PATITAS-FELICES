<?php

namespace Tests\Feature;

use App\Services\AuthService;
use App\Services\CitaService;
use App\Services\InventarioService;
use App\Services\MascotaService;
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
        $this->assertTrue(class_exists(CitaService::class));
        $this->assertTrue(class_exists(InventarioService::class));
        $this->assertTrue(class_exists(VentaService::class));
    }
}
