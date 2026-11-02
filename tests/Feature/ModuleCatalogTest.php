<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Modulo;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\User;
use App\Support\CatalogoDeModulos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class ModuleCatalogTest extends TestCase
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

    public function test_sync_seeds_the_catalog_even_when_config_is_unavailable(): void
    {
        $admin = $this->rol('admin');
        $this->rol('director');

        Permiso::query()->delete();
        Modulo::query()->delete();

        // Simulates a stale `bootstrap/cache/config.php` that predates the
        // catalog file: `config()` cannot see it, the file on disk can.
        config(['modulos' => []]);

        $resultado = CatalogoDeModulos::sincronizar();

        $this->assertSame(22, Modulo::query()->count());
        $this->assertSame(22, $resultado['modulos_creados']);
        $this->assertSame(44, Permiso::query()->count());

        $roles = Modulo::query()->where('clave', 'roles')->firstOrFail();

        $permiso = Permiso::query()
            ->where('rol_id', $admin->id)
            ->where('modulo_id', $roles->id)
            ->firstOrFail();

        $this->assertTrue($permiso->puede_ver);
        $this->assertFalse($permiso->puede_escribir);
        $this->assertFalse($permiso->puede_eliminar);
    }

    public function test_sync_is_idempotent_and_never_overwrites_a_customized_matrix(): void
    {
        $admin = $this->rol('admin');

        CatalogoDeModulos::sincronizar();

        $modulo = Modulo::query()->where('clave', 'escuelas')->firstOrFail();

        Permiso::query()
            ->where('rol_id', $admin->id)
            ->where('modulo_id', $modulo->id)
            ->update(['puede_ver' => false, 'puede_escribir' => false, 'puede_eliminar' => false]);

        $resultado = CatalogoDeModulos::sincronizar();

        $this->assertSame(0, $resultado['modulos_creados']);
        $this->assertSame(0, $resultado['permisos_creados']);
        $this->assertSame(22, Modulo::query()->count());

        $this->assertFalse(
            Permiso::query()
                ->where('rol_id', $admin->id)
                ->where('modulo_id', $modulo->id)
                ->value('puede_ver'),
        );
    }

    public function test_the_sync_command_reports_what_it_did(): void
    {
        $this->artisan('modulos:sincronizar')
            ->expectsOutputToContain('Catalog synced')
            ->assertSuccessful();

        $this->assertSame(22, Modulo::query()->count());
    }

    public function test_an_empty_catalog_fails_closed_instead_of_opening_every_route(): void
    {
        $admin = $this->usuario('admin');

        Permiso::query()->delete();
        Modulo::query()->delete();

        $this->actingAs($admin)->get(route('pagos.index'))->assertStatus(503);
    }

    public function test_super_admin_can_still_reach_roles_to_repair_an_empty_catalog(): void
    {
        $superAdmin = $this->usuario('super-admin');

        Permiso::query()->delete();
        Modulo::query()->delete();

        $this->actingAs($superAdmin)->get(route('roles.index'))->assertOk();
    }

    public function test_export_roles_are_read_from_the_definition_not_from_config(): void
    {
        config(['modulos' => []]);

        $this->assertSame(
            ['admin', 'super-admin', 'director', 'recepcion'],
            CatalogoDeModulos::rolesExportacion('pagos-talleres'),
        );

        $this->assertSame(['admin', 'super-admin'], CatalogoDeModulos::rolesExportacion('alumnos'));
    }

    public function test_export_roles_fall_back_to_the_default_for_an_unknown_module(): void
    {
        config(['modulos' => []]);

        $this->assertSame(
            ['admin', 'super-admin'],
            CatalogoDeModulos::rolesExportacion('modulo-inexistente'),
        );
    }

    public function test_a_staff_role_can_export_a_payment_submodule_without_cached_config(): void
    {
        $recepcion = $this->usuario('recepcion');

        // Proves the middleware reads the export allowlist from the definition
        // file: with config() emptied this request would be denied.
        config(['modulos' => []]);

        $this->actingAs($recepcion)
            ->get(route('pagos-talleres.export.excel'))
            ->assertOk();

        $this->actingAs($recepcion)
            ->get(route('alumnos.export.excel'))
            ->assertForbidden();
    }

    public function test_a_new_role_gets_its_baseline_matrix_without_cached_config(): void
    {
        config(['modulos' => []]);

        $admin = Rol::factory()->state(['nombre' => 'Administrador', 'slug' => 'admin'])->create();

        $escuelas = Modulo::query()->where('clave', 'escuelas')->firstOrFail();

        $permiso = Permiso::query()
            ->where('rol_id', $admin->id)
            ->where('modulo_id', $escuelas->id)
            ->firstOrFail();

        $this->assertTrue($permiso->puede_ver);
        $this->assertTrue($permiso->puede_escribir);
    }

    public function test_a_route_prefix_outside_the_catalog_is_denied(): void
    {
        Route::middleware(['web', 'permiso'])
            ->get('/__modulo-desconocido', fn (): string => 'ok')
            ->name('modulo-desconocido.index');

        $admin = $this->usuario('admin');

        $this->actingAs($admin)->get('/__modulo-desconocido')->assertForbidden();
    }
}
