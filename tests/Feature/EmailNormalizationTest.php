<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\GradoEscolar;
use App\Models\PortalUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class EmailNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_persists_email_in_lowercase(): void
    {
        $user = User::factory()->create(['email' => 'MiXeD@Example.COM']);

        $this->assertSame('mixed@example.com', $user->email);
        $this->assertSame('mixed@example.com', $user->fresh()->email);
        $this->assertDatabaseHas('users', ['email' => 'mixed@example.com']);
        $this->assertDatabaseMissing('users', ['email' => 'MiXeD@Example.COM']);
    }

    public function test_portal_user_model_persists_email_in_lowercase(): void
    {
        $portalUser = PortalUser::factory()->create(['email' => 'PoRtAl@Example.COM']);

        $this->assertSame('portal@example.com', $portalUser->email);
        $this->assertSame('portal@example.com', $portalUser->fresh()->email);
        $this->assertDatabaseHas('portal_users', ['email' => 'portal@example.com']);
        $this->assertDatabaseMissing('portal_users', ['email' => 'PoRtAl@Example.COM']);
    }

    public function test_admin_can_log_in_with_uppercase_email(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->post(route('login.attempt'), [
            'login' => 'ADMIN@EXAMPLE.COM',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_portal_user_can_log_in_with_uppercase_email(): void
    {
        $grado = GradoEscolar::factory()->create();

        PortalUser::factory()->create([
            'email' => 'portal@example.com',
            'grado_escolar_id' => $grado->id,
            'password' => 'password',
            'must_change_password' => false,
        ]);

        $this->post(route('portal.login.attempt'), [
            'email' => 'PORTAL@EXAMPLE.COM',
            'password' => 'password',
        ])->assertRedirect(route('portal.dashboard'));

        $this->assertAuthenticatedAs(PortalUser::first(), 'portal');
    }

    public function test_store_rejects_uppercase_email_that_already_exists_in_lowercase(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($admin)->post(route('usuarios.store'), [
            'name' => 'Duplicado',
            'email' => 'EXISTING@EXAMPLE.COM',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_portal_registration_does_not_create_duplicate_for_different_casing(): void
    {
        Mail::fake();

        $grado = GradoEscolar::factory()->create();
        PortalUser::factory()->create([
            'email' => 'juan@example.com',
            'grado_escolar_id' => $grado->id,
        ]);

        $this->post(route('portal.register.attempt'), [
            'name' => 'Juan Pérez',
            'email' => 'JUAN@EXAMPLE.COM',
            'alumno_nombre' => 'Ana Pérez',
            'grado_escolar_id' => $grado->id,
        ])
            ->assertRedirect(route('portal.login'))
            ->assertSessionHas('status')
            ->assertSessionDoesntHaveErrors(['email']);

        $this->assertDatabaseCount('portal_users', 1);
        Mail::assertNothingSent();
    }
}
