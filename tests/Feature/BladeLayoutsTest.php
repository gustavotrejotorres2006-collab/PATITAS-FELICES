<?php

namespace Tests\Feature;

use Tests\TestCase;

class BladeLayoutsTest extends TestCase
{
    public function test_public_home_uses_the_public_site_layout(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Cuidado veterinario')
            ->assertSee('Servicios')
            ->assertSee('Saltar al contenido')
            ->assertSee('plus-jakarta-sans-400-normal');
    }

    public function test_private_dashboard_renders_the_portal_layout(): void
    {
        $this->view('portal.dashboard')
            ->assertSee('Resumen del portal')
            ->assertSee('Navegación del portal')
            ->assertSee('Pacientes registrados')
            ->assertDontSee('Cerrar sesión');
    }
}
