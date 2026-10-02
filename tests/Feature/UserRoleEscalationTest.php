<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserRoleEscalationTest extends TestCase
{
    use RefreshDatabase;

    private function role(string $slug): Rol
    {
        return Rol::query()->firstOrCreate(
            ['slug' => $slug],
            ['nombre' => ucfirst($slug)],
        );
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    public function test_role_id_is_not_mass_assignable(): void
    {
        $this->assertFalse((new User)->isFillable('role_id'));
    }

    public function test_admin_cannot_create_user_with_super_admin_role(): void
    {
        $admin = User::factory()->admin()->create();
        $superAdminRole = $this->role('super-admin');

        $response = $this->actingAs($admin)->post(route('usuarios.store'), $this->payload([
            'email' => 'escalated@example.com',
            'role_id' => $superAdminRole->id,
        ]));

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'escalated@example.com']);
    }

    public function test_super_admin_can_create_user_with_super_admin_role(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $superAdminRole = $this->role('super-admin');

        $response = $this->actingAs($superAdmin)->post(route('usuarios.store'), $this->payload([
            'email' => 'new-super@example.com',
            'role_id' => $superAdminRole->id,
        ]));

        $response->assertRedirect(route('usuarios.index'));

        $created = User::where('email', 'new-super@example.com')->firstOrFail();
        $this->assertSame($superAdminRole->id, $created->role_id);
    }

    public function test_admin_cannot_edit_super_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('usuarios.edit', $target))
            ->assertForbidden();

        $this->actingAs($admin)
            ->put(route('usuarios.update', $target), [
                'name' => 'Hacked',
                'email' => $target->email,
            ])
            ->assertForbidden();

        $this->assertSame($target->name, $target->fresh()->name);
    }

    public function test_admin_cannot_delete_super_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->delete(route('usuarios.destroy', $target))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }

    public function test_super_admin_can_update_another_user(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $target = User::factory()->director()->create();

        $this->actingAs($superAdmin)
            ->put(route('usuarios.update', $target), [
                'name' => 'Nombre Actualizado',
                'email' => $target->email,
            ])
            ->assertRedirect(route('usuarios.index'));

        $this->assertSame('Nombre Actualizado', $target->fresh()->name);
    }

    public function test_admin_cannot_self_escalate_to_super_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $superAdminRole = $this->role('super-admin');

        $this->actingAs($admin)
            ->put(route('usuarios.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role_id' => $superAdminRole->id,
            ])
            ->assertForbidden();

        $this->assertNotSame($superAdminRole->id, $admin->fresh()->role_id);
    }

    public function test_admin_can_create_and_edit_users_with_non_super_admin_roles(): void
    {
        $admin = User::factory()->admin()->create();
        $directorRole = $this->role('director');
        $recepcionRole = $this->role('recepcion');

        $this->actingAs($admin)->post(route('usuarios.store'), $this->payload([
            'email' => 'director@example.com',
            'role_id' => $directorRole->id,
        ]))->assertRedirect(route('usuarios.index'));

        $created = User::where('email', 'director@example.com')->firstOrFail();
        $this->assertSame($directorRole->id, $created->role_id);

        $this->actingAs($admin)->put(route('usuarios.update', $created), [
            'name' => $created->name,
            'email' => $created->email,
            'role_id' => $recepcionRole->id,
        ])->assertRedirect(route('usuarios.index'));

        $this->assertSame($recepcionRole->id, $created->fresh()->role_id);
    }

    public function test_user_cannot_delete_itself(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->delete(route('usuarios.destroy', $admin))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->delete(route('usuarios.destroy', $superAdmin))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
    }
}
