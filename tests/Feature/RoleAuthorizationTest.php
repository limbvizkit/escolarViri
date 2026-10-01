<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\AlumnoArchivo;
use App\Models\Taller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_from_admin_panel(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_without_role_receives_forbidden(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertForbidden();
    }

    public function test_admin_role_retains_full_access(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->actingAs($user)->get(route('alumnos.index'))->assertOk();
        $this->actingAs($user)->get(route('pagos.index'))->assertOk();
        $this->actingAs($user)->get(route('adeudos.index'))->assertOk();
        $this->actingAs($user)->get(route('talleres.index'))->assertOk();
        $this->actingAs($user)->get(route('documentacion.index'))->assertOk();
        $this->actingAs($user)->get(route('academic-documents.index'))->assertOk();
        $this->actingAs($user)->get(route('usuarios.index'))->assertOk();
        $this->actingAs($user)->get(route('roles.index'))->assertOk();
        $this->actingAs($user)->get(route('empleados.index'))->assertOk();
        $this->actingAs($user)->get(route('escuelas.index'))->assertOk();
        $this->actingAs($user)->get(route('alumnos.export.pdf'))->assertOk();
        $this->actingAs($user)->get(route('online-payments.index'))->assertOk();
    }

    public function test_super_admin_role_retains_full_access(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->actingAs($user)->get(route('alumnos.index'))->assertOk();
        $this->actingAs($user)->get(route('pagos.index'))->assertOk();
        $this->actingAs($user)->get(route('adeudos.index'))->assertOk();
        $this->actingAs($user)->get(route('talleres.index'))->assertOk();
        $this->actingAs($user)->get(route('documentacion.index'))->assertOk();
        $this->actingAs($user)->get(route('usuarios.index'))->assertOk();
        $this->actingAs($user)->get(route('empleados.export.excel'))->assertOk();
        $this->actingAs($user)->get(route('online-payments.export.pdf'))->assertOk();
    }

    public function test_director_can_access_dashboard(): void
    {
        $user = User::factory()->director()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }

    public function test_director_can_access_alumnos_pagos_adeudos_documentacion_and_talleres(): void
    {
        $user = User::factory()->director()->create();

        $this->actingAs($user)->get(route('alumnos.index'))->assertOk();
        $this->actingAs($user)->get(route('pagos.index'))->assertOk();
        $this->actingAs($user)->get(route('adeudos.index'))->assertOk();
        $this->actingAs($user)->get(route('documentacion.index'))->assertOk();
        $this->actingAs($user)->get(route('academic-documents.index'))->assertOk();
        $this->actingAs($user)->get(route('talleres.index'))->assertOk();
    }

    public function test_director_can_write_alumnos_pagos_adeudos_documentacion_and_talleres(): void
    {
        $user = User::factory()->director()->create();
        $alumno = Alumno::factory()->create();

        $this->actingAs($user)->get(route('alumnos.create'))->assertOk();
        $this->actingAs($user)->get(route('alumnos.edit', $alumno))->assertOk();
        $this->actingAs($user)->get(route('pagos.create'))->assertOk();
        $this->actingAs($user)->get(route('adeudos.create'))->assertOk();
        $this->actingAs($user)->get(route('talleres.create'))->assertOk();
    }

    public function test_director_is_forbidden_from_usuarios_roles_empleados_and_configuration(): void
    {
        $user = User::factory()->director()->create();

        $this->actingAs($user)->get(route('usuarios.index'))->assertForbidden();
        $this->actingAs($user)->get(route('roles.index'))->assertForbidden();
        $this->actingAs($user)->get(route('empleados.index'))->assertForbidden();
        $this->actingAs($user)->get(route('escuelas.index'))->assertForbidden();
        $this->actingAs($user)->get(route('sucursales.index'))->assertForbidden();
        $this->actingAs($user)->get(route('grados-escolares.index'))->assertForbidden();
    }

    public function test_director_is_forbidden_from_bulk_exports_and_online_payments(): void
    {
        $user = User::factory()->director()->create();

        $this->actingAs($user)->get(route('alumnos.export.pdf'))->assertForbidden();
        $this->actingAs($user)->get(route('alumnos.export.excel'))->assertForbidden();
        $this->actingAs($user)->get(route('pagos.export.pdf'))->assertForbidden();
        $this->actingAs($user)->get(route('pagos.export.excel'))->assertForbidden();
        $this->actingAs($user)->get(route('talleres.export.pdf'))->assertForbidden();
        $this->actingAs($user)->get(route('talleres.export.excel'))->assertForbidden();
        $this->actingAs($user)->get(route('adeudos.export.pdf'))->assertForbidden();
        $this->actingAs($user)->get(route('adeudos.export.excel'))->assertForbidden();
        $this->actingAs($user)->get(route('empleados.export.pdf'))->assertForbidden();
        $this->actingAs($user)->get(route('online-payments.index'))->assertForbidden();
    }

    public function test_recepcion_can_access_alumnos_pagos_adeudos_and_documentacion(): void
    {
        $user = User::factory()->recepcion()->create();

        $this->actingAs($user)->get(route('alumnos.index'))->assertOk();
        $this->actingAs($user)->get(route('pagos.index'))->assertOk();
        $this->actingAs($user)->get(route('adeudos.index'))->assertOk();
        $this->actingAs($user)->get(route('documentacion.index'))->assertOk();
        $this->actingAs($user)->get(route('academic-documents.index'))->assertOk();
    }

    public function test_recepcion_can_write_alumnos_pagos_adeudos_and_documentacion(): void
    {
        $user = User::factory()->recepcion()->create();
        $alumno = Alumno::factory()->create();

        $this->actingAs($user)->get(route('alumnos.create'))->assertOk();
        $this->actingAs($user)->get(route('alumnos.edit', $alumno))->assertOk();
        $this->actingAs($user)->get(route('pagos.create'))->assertOk();
        $this->actingAs($user)->get(route('adeudos.create'))->assertOk();
    }

    public function test_recepcion_is_forbidden_from_dashboard(): void
    {
        $user = User::factory()->recepcion()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertForbidden();
    }

    public function test_recepcion_is_forbidden_from_talleres(): void
    {
        $user = User::factory()->recepcion()->create();

        $this->actingAs($user)->get(route('talleres.index'))->assertForbidden();
        $this->actingAs($user)->get(route('talleres.create'))->assertForbidden();
    }

    public function test_recepcion_is_forbidden_from_bulk_exports(): void
    {
        $user = User::factory()->recepcion()->create();

        $this->actingAs($user)->get(route('alumnos.export.pdf'))->assertForbidden();
        $this->actingAs($user)->get(route('alumnos.export.excel'))->assertForbidden();
        $this->actingAs($user)->get(route('pagos.export.pdf'))->assertForbidden();
        $this->actingAs($user)->get(route('talleres.export.pdf'))->assertForbidden();
        $this->actingAs($user)->get(route('adeudos.export.pdf'))->assertForbidden();
        $this->actingAs($user)->get(route('empleados.export.pdf'))->assertForbidden();
    }

    public function test_recepcion_is_forbidden_from_usuarios_roles_empleados_and_configuration(): void
    {
        $user = User::factory()->recepcion()->create();

        $this->actingAs($user)->get(route('usuarios.index'))->assertForbidden();
        $this->actingAs($user)->get(route('roles.index'))->assertForbidden();
        $this->actingAs($user)->get(route('empleados.index'))->assertForbidden();
        $this->actingAs($user)->get(route('escuelas.index'))->assertForbidden();
        $this->actingAs($user)->get(route('sucursales.index'))->assertForbidden();
    }

    public function test_profesor_can_access_dashboard(): void
    {
        $user = User::factory()->profesor()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }

    public function test_profesor_can_read_alumnos_and_talleres(): void
    {
        $user = User::factory()->profesor()->create();
        $alumno = Alumno::factory()->create();

        $this->actingAs($user)->get(route('alumnos.index'))->assertOk();
        $this->actingAs($user)->get(route('alumnos.show', $alumno))->assertOk();
        $this->actingAs($user)->get(route('talleres.index'))->assertOk();
    }

    public function test_profesor_is_forbidden_from_writing_alumnos(): void
    {
        $user = User::factory()->profesor()->create();
        $alumno = Alumno::factory()->create();

        $this->actingAs($user)->get(route('alumnos.create'))->assertForbidden();
        $this->actingAs($user)->post(route('alumnos.store'))->assertForbidden();
        $this->actingAs($user)->get(route('alumnos.edit', $alumno))->assertForbidden();
        $this->actingAs($user)->put(route('alumnos.update', $alumno))->assertForbidden();
        $this->actingAs($user)->delete(route('alumnos.destroy', $alumno))->assertForbidden();
        $this->actingAs($user)->put(route('alumnos.inline-update', $alumno))->assertForbidden();
    }

    public function test_profesor_is_forbidden_from_alumnos_archivos(): void
    {
        $user = User::factory()->profesor()->create();
        $archivo = AlumnoArchivo::factory()->create();
        $alumno = $archivo->alumno;

        $this->actingAs($user)->post(route('alumnos.archivos.store', $alumno))->assertForbidden();
        $this->actingAs($user)->get(route('alumnos.archivos.download', [$alumno, $archivo]))->assertForbidden();
        $this->actingAs($user)->delete(route('alumnos.archivos.destroy', [$alumno, $archivo]))->assertForbidden();
    }

    public function test_profesor_is_forbidden_from_pagos_adeudos_and_documentacion(): void
    {
        $user = User::factory()->profesor()->create();

        $this->actingAs($user)->get(route('pagos.index'))->assertForbidden();
        $this->actingAs($user)->get(route('pagos.create'))->assertForbidden();
        $this->actingAs($user)->get(route('adeudos.index'))->assertForbidden();
        $this->actingAs($user)->get(route('documentacion.index'))->assertForbidden();
        $this->actingAs($user)->get(route('academic-documents.index'))->assertForbidden();
    }

    public function test_profesor_is_forbidden_from_writing_talleres(): void
    {
        $user = User::factory()->profesor()->create();
        $taller = Taller::create(['nombre' => 'Taller de prueba', 'costo' => 100]);

        $this->actingAs($user)->get(route('talleres.create'))->assertForbidden();
        $this->actingAs($user)->post(route('talleres.store'))->assertForbidden();
        $this->actingAs($user)->get(route('talleres.edit', $taller))->assertForbidden();
        $this->actingAs($user)->put(route('talleres.update', $taller))->assertForbidden();
        $this->actingAs($user)->delete(route('talleres.destroy', $taller))->assertForbidden();
    }

    public function test_profesor_is_forbidden_from_exports_and_admin_routes(): void
    {
        $user = User::factory()->profesor()->create();

        $this->actingAs($user)->get(route('alumnos.export.pdf'))->assertForbidden();
        $this->actingAs($user)->get(route('pagos.export.excel'))->assertForbidden();
        $this->actingAs($user)->get(route('talleres.export.pdf'))->assertForbidden();
        $this->actingAs($user)->get(route('adeudos.export.pdf'))->assertForbidden();
        $this->actingAs($user)->get(route('empleados.export.pdf'))->assertForbidden();

        $this->actingAs($user)->get(route('usuarios.index'))->assertForbidden();
        $this->actingAs($user)->get(route('roles.index'))->assertForbidden();
        $this->actingAs($user)->get(route('empleados.index'))->assertForbidden();
        $this->actingAs($user)->get(route('escuelas.index'))->assertForbidden();
    }

    public function test_public_login_route_remains_accessible(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    public function test_public_logout_route_remains_functional(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
    }

    public function test_openpay_webhook_route_remains_unprotected(): void
    {
        config(['openpay.webhook_user' => 'webhook-user']);
        config(['openpay.webhook_password' => 'webhook-pass']);

        $response = $this->postJson(route('openpay.webhook'));

        $response->assertUnauthorized();
    }
}
