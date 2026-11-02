<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Escuela;
use App\Models\Estatus;
use App\Models\Modulo;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ModulePermissionTest extends TestCase
{
    use RefreshDatabase;

    private function rol(string $slug): Rol
    {
        return Rol::query()->where('slug', $slug)->first()
            ?? Rol::factory()->state([
                'nombre' => ucfirst(str_replace('-', ' ', $slug)),
                'slug' => $slug,
            ])->create();
    }

    private function usuario(string $slug): User
    {
        return User::factory()->create(['role_id' => $this->rol($slug)->id]);
    }

    /**
     * Build a complete matrix payload for a role, so a single override never
     * wipes the modules the test does not care about.
     *
     * @param  array<string, array<string, int>>  $overrides
     * @return array<int, array<string, int>>
     */
    private function payloadPermisos(Rol $rol, array $overrides = []): array
    {
        $rol->load('permisos');

        $permisos = [];

        foreach (Modulo::query()->ordenado()->get() as $modulo) {
            $actual = $rol->permisos->firstWhere('modulo_id', $modulo->id);

            $permisos[$modulo->id] = array_merge([
                'ver' => (int) ($actual?->puede_ver ?? false),
                'escribir' => (int) ($actual?->puede_escribir ?? false),
                'eliminar' => (int) ($actual?->puede_eliminar ?? false),
            ], $overrides[$modulo->clave] ?? []);
        }

        return $permisos;
    }

    public function test_super_admin_reaches_every_module_index_and_bypasses_the_matrix(): void
    {
        $superAdmin = $this->usuario('super-admin');
        $rol = $superAdmin->rol;

        $this->assertDatabaseMissing('permisos', ['rol_id' => $rol->id]);

        foreach (config('modulos.modulos') as $modulo) {
            $this->actingAs($superAdmin)
                ->get(route($modulo['ruta']))
                ->assertOk();
        }

        // Even an explicit all-false matrix must not affect a super-admin.
        $now = now();

        Permiso::query()->insert(
            Modulo::query()->get()->map(fn (Modulo $modulo): array => [
                'rol_id' => $rol->id,
                'modulo_id' => $modulo->id,
                'puede_ver' => false,
                'puede_escribir' => false,
                'puede_eliminar' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all(),
        );

        $superAdmin = $superAdmin->fresh();

        $this->assertTrue($superAdmin->puedeVer('escuelas'));
        $this->actingAs($superAdmin)->get(route('escuelas.index'))->assertOk();
    }

    public function test_admin_reaches_every_module_index(): void
    {
        $admin = $this->usuario('admin');

        foreach (config('modulos.modulos') as $modulo) {
            $this->actingAs($admin)
                ->get(route($modulo['ruta']))
                ->assertOk();
        }
    }

    public function test_admin_can_read_but_not_write_roles(): void
    {
        $admin = $this->usuario('admin');
        $objetivo = $this->rol('director');

        $this->actingAs($admin)->get(route('roles.index'))->assertOk();
        $this->actingAs($admin)->get(route('roles.show', $objetivo))->assertOk();

        $this->actingAs($admin)->get(route('roles.create'))->assertForbidden();
        $this->actingAs($admin)->post(route('roles.store'), ['nombre' => 'Nuevo rol'])->assertForbidden();
        $this->actingAs($admin)->get(route('roles.edit', $objetivo))->assertForbidden();
        $this->actingAs($admin)->put(route('roles.update', $objetivo), ['nombre' => 'Otro nombre'])->assertForbidden();
        $this->actingAs($admin)->delete(route('roles.destroy', $objetivo))->assertForbidden();

        $this->assertDatabaseHas('roles', ['id' => $objetivo->id, 'nombre' => 'Director']);
    }

    public function test_admin_can_still_manage_module_records(): void
    {
        $admin = $this->usuario('admin');

        $this->actingAs($admin)->post(route('escuelas.store'), [
            'nombre' => 'Escuela Nueva',
            'clave' => 'ESC-NEW-1',
        ])->assertRedirect(route('escuelas.index'));

        $escuela = Escuela::query()->where('clave', 'ESC-NEW-1')->firstOrFail();

        $this->actingAs($admin)->put(route('escuelas.update', $escuela), [
            'nombre' => 'Escuela Renombrada',
            'clave' => 'ESC-NEW-1',
        ])->assertRedirect(route('escuelas.index'));

        $this->assertSame('Escuela Renombrada', $escuela->fresh()->nombre);

        $this->actingAs($admin)->delete(route('escuelas.destroy', $escuela))
            ->assertRedirect(route('escuelas.index'));

        $this->assertSame(Estatus::ELIMINADO, $escuela->fresh()->estatus_id);
    }

    public function test_admin_cannot_mutate_the_permission_matrix(): void
    {
        $admin = $this->usuario('admin');
        $recepcion = $this->rol('recepcion');
        $modulo = Modulo::query()->where('clave', 'pagos')->firstOrFail();

        $antes = Permiso::query()
            ->where('rol_id', $recepcion->id)
            ->where('modulo_id', $modulo->id)
            ->value('puede_ver');

        $this->actingAs($admin)->put(route('roles.update', $recepcion), [
            'nombre' => $recepcion->nombre,
            'slug' => $recepcion->slug,
            'permisos' => $this->payloadPermisos($recepcion, [
                'pagos' => ['ver' => 0, 'escribir' => 0, 'eliminar' => 0],
            ]),
        ])->assertForbidden();

        $despues = Permiso::query()
            ->where('rol_id', $recepcion->id)
            ->where('modulo_id', $modulo->id)
            ->value('puede_ver');

        $this->assertSame($antes, $despues);
    }

    public function test_super_admin_can_mutate_the_matrix_and_it_takes_effect(): void
    {
        $superAdmin = $this->usuario('super-admin');
        $recepcion = $this->rol('recepcion');
        $modulo = Modulo::query()->where('clave', 'adeudos')->firstOrFail();

        $this->actingAs($this->usuario('recepcion'))->get(route('adeudos.index'))->assertOk();

        $this->actingAs($superAdmin)->put(route('roles.update', $recepcion), [
            'nombre' => $recepcion->nombre,
            'slug' => $recepcion->slug,
            'permisos' => $this->payloadPermisos($recepcion, [
                'adeudos' => ['ver' => 0, 'escribir' => 0, 'eliminar' => 0],
            ]),
        ])->assertRedirect(route('roles.index'));

        $this->assertDatabaseHas('permisos', [
            'rol_id' => $recepcion->id,
            'modulo_id' => $modulo->id,
            'puede_ver' => false,
        ]);

        $this->actingAs($this->usuario('recepcion'))->get(route('adeudos.index'))->assertForbidden();

        $this->actingAs($superAdmin)->put(route('roles.update', $recepcion), [
            'nombre' => $recepcion->nombre,
            'slug' => $recepcion->slug,
            'permisos' => $this->payloadPermisos($recepcion, [
                'adeudos' => ['ver' => 1, 'escribir' => 0, 'eliminar' => 0],
            ]),
        ])->assertRedirect(route('roles.index'));

        $this->actingAs($this->usuario('recepcion'))->get(route('adeudos.index'))->assertOk();
    }

    public function test_write_permission_implies_view_permission(): void
    {
        $superAdmin = $this->usuario('super-admin');
        $admin = $this->rol('admin');
        $modulo = Modulo::query()->where('clave', 'escuelas')->firstOrFail();

        $this->actingAs($superAdmin)->put(route('roles.update', $admin), [
            'nombre' => $admin->nombre,
            'slug' => $admin->slug,
            'permisos' => $this->payloadPermisos($admin, [
                'escuelas' => ['ver' => 0, 'escribir' => 1, 'eliminar' => 0],
            ]),
        ])->assertRedirect(route('roles.index'));

        $this->assertDatabaseHas('permisos', [
            'rol_id' => $admin->id,
            'modulo_id' => $modulo->id,
            'puede_ver' => true,
            'puede_escribir' => true,
        ]);
    }

    public function test_role_without_permission_rows_is_denied(): void
    {
        $rol = Rol::factory()->create(['slug' => 'sin-permisos']);
        $rol->permisos()->delete();

        $usuario = User::factory()->create(['role_id' => $rol->id]);

        $this->actingAs($usuario)->get(route('escuelas.index'))->assertForbidden();
        $this->assertFalse($usuario->puedeVer('escuelas'));
    }

    public function test_profesor_sees_only_the_seeded_modules(): void
    {
        $profesor = $this->usuario('profesor');

        $this->assertSame(
            ['dashboard', 'talleres', 'cursos', 'alumnos', 'estimulacion-temprana'],
            $profesor->modulosVisibles()->pluck('clave')->all(),
        );

        $this->actingAs($profesor)->get(route('pagos.index'))->assertForbidden();
    }

    public function test_recepcion_is_denied_dashboard(): void
    {
        $recepcion = $this->usuario('recepcion');

        $this->assertFalse($recepcion->puedeVer('dashboard'));
        $this->actingAs($recepcion)->get(route('dashboard'))->assertForbidden();
    }

    public function test_sidebar_renders_only_allowed_module_links(): void
    {
        $profesor = $this->usuario('profesor');

        $response = $this->actingAs($profesor)->get(route('dashboard'));

        $response->assertOk();

        foreach (['talleres.index', 'cursos.index', 'alumnos.index', 'estimulacion-temprana.index'] as $ruta) {
            $response->assertSee('href="'.route($ruta).'"', false);
        }

        foreach (['pagos.index', 'adeudos.index', 'roles.index', 'usuarios.index', 'escuelas.index'] as $ruta) {
            $response->assertDontSee('href="'.route($ruta).'"', false);
        }
    }

    public function test_super_admin_sees_the_permission_matrix_on_the_role_form(): void
    {
        $superAdmin = $this->usuario('super-admin');
        $rol = $this->rol('recepcion');
        $pagos = Modulo::query()->where('clave', 'pagos')->firstOrFail();

        $create = $this->actingAs($superAdmin)->get(route('roles.create'));

        $create->assertOk();
        $create->assertSee('Permisos por módulo');
        $create->assertSee('name="permisos['.$pagos->id.'][ver]"', false);

        $edit = $this->actingAs($superAdmin)->get(route('roles.edit', $rol));

        $edit->assertOk();
        $edit->assertSee('name="permisos['.$pagos->id.'][ver]"', false);
        $edit->assertSee('var editable = true', false);
    }

    public function test_super_admin_sidebar_keeps_every_module(): void
    {
        $superAdmin = $this->usuario('super-admin');

        $response = $this->actingAs($superAdmin)->get(route('dashboard'));

        $response->assertOk();

        foreach (config('modulos.modulos') as $modulo) {
            $response->assertSee('href="'.route($modulo['ruta']).'"', false);
        }
    }

    public function test_export_boundaries_match_the_pre_matrix_roles(): void
    {
        $recepcion = $this->usuario('recepcion');
        $director = $this->usuario('director');

        // The five payment submodules exported for staff before the matrix existed.
        foreach (['pagos-talleres', 'pagos-cursos', 'pagos-lunch', 'pagos-8am', 'pagos-horarios-extendidos'] as $modulo) {
            $this->actingAs($recepcion)->get(route($modulo.'.export.excel'))->assertOk();
        }

        $this->actingAs($director)->get(route('pagos-talleres.export.pdf'))->assertOk();

        // Every other module stayed admin-only.
        $this->actingAs($recepcion)->get(route('alumnos.export.excel'))->assertForbidden();
        $this->actingAs($director)->get(route('pagos.export.pdf'))->assertForbidden();
    }
}
