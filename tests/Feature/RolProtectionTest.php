<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RolProtectionTest extends TestCase
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

    public function test_super_admin_role_slug_cannot_be_changed(): void
    {
        $actor = $this->usuario('super-admin');
        $protegido = $this->rol('super-admin');

        $this->actingAs($actor)
            ->put(route('roles.update', $protegido), [
                'nombre' => 'Super Administrador',
                'slug' => 'root',
            ])
            ->assertSessionHasErrors('slug');

        $this->assertSame('super-admin', $protegido->fresh()->slug);
        $this->assertTrue($actor->fresh()->esSuperAdmin());
    }

    public function test_renaming_the_super_admin_role_keeps_its_slug(): void
    {
        $actor = $this->usuario('super-admin');
        $protegido = $this->rol('super-admin');

        $this->actingAs($actor)
            ->put(route('roles.update', $protegido), [
                'nombre' => 'Administrador General',
            ])
            ->assertRedirect(route('roles.index'));

        $protegido->refresh();

        $this->assertSame('Administrador General', $protegido->nombre);
        $this->assertSame('super-admin', $protegido->slug);
    }

    public function test_super_admin_role_cannot_be_deleted(): void
    {
        $actor = $this->usuario('super-admin');
        $protegido = $this->rol('super-admin');

        $this->actingAs($actor)
            ->delete(route('roles.destroy', $protegido))
            ->assertForbidden();

        $this->assertDatabaseHas('roles', ['id' => $protegido->id, 'slug' => 'super-admin']);
    }

    public function test_the_delete_action_is_hidden_for_the_protected_role(): void
    {
        $actor = $this->usuario('super-admin');

        $response = $this->actingAs($actor)->get(route('roles.index'));

        $response->assertOk();
        $response->assertDontSee('action="'.route('roles.destroy', $this->rol('super-admin')).'"', false);
    }

    public function test_regular_roles_can_still_change_their_slug_and_be_deleted(): void
    {
        $actor = $this->usuario('super-admin');
        $rol = $this->rol('director');

        $this->actingAs($actor)
            ->put(route('roles.update', $rol), [
                'nombre' => 'Director',
                'slug' => 'director-academico',
            ])
            ->assertRedirect(route('roles.index'));

        $this->assertSame('director-academico', $rol->fresh()->slug);

        $this->actingAs($actor)
            ->delete(route('roles.destroy', $rol))
            ->assertRedirect(route('roles.index'));

        $this->assertDatabaseMissing('roles', ['id' => $rol->id]);
    }
}
