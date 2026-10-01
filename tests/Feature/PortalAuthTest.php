<?php

namespace Tests\Feature;

use App\Mail\PortalUserRegistered;
use App\Models\GradoEscolar;
use App\Models\PortalUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PortalAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_is_accessible(): void
    {
        GradoEscolar::factory()->create();

        $this->get(route('portal.register'))
            ->assertOk()
            ->assertSee('Crear cuenta del portal')
            ->assertSee('Registrarme');
    }

    public function test_guest_can_register_and_receives_temporary_password_by_email(): void
    {
        Mail::fake();

        $grado = GradoEscolar::factory()->create();

        $response = $this->post(route('portal.register.attempt'), [
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'phone' => '5551234567',
            'alumno_nombre' => 'Ana Pérez',
            'grado_escolar_id' => $grado->id,
        ]);

        $response->assertRedirect(route('portal.login'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('portal_users', [
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'phone' => '5551234567',
            'alumno_nombre' => 'Ana Pérez',
            'grado_escolar_id' => $grado->id,
            'must_change_password' => true,
        ]);

        $portalUser = PortalUser::where('email', 'juan@example.com')->first();
        $this->assertNotNull($portalUser);

        $tempPassword = null;
        Mail::assertSent(PortalUserRegistered::class, function ($mail) use (&$tempPassword, $portalUser) {
            $tempPassword = $mail->tempPassword;

            return $mail->hasTo($portalUser->email)
                && $mail->portalUser->is($portalUser);
        });

        $this->assertNotNull($tempPassword);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{12}$/', $tempPassword);
        $this->assertNotEquals($tempPassword, $portalUser->password);
        $this->assertTrue(Hash::check($tempPassword, $portalUser->password));
        $this->assertTrue($portalUser->must_change_password);
    }

    public function test_registration_does_not_reveal_existing_email(): void
    {
        Mail::fake();

        $grado = GradoEscolar::factory()->create();
        PortalUser::factory()->create(['email' => 'juan@example.com', 'grado_escolar_id' => $grado->id]);

        $this->post(route('portal.register.attempt'), [
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'alumno_nombre' => 'Ana Pérez',
            'grado_escolar_id' => $grado->id,
        ])
            ->assertRedirect(route('portal.login'))
            ->assertSessionHas('status')
            ->assertSessionDoesntHaveErrors(['email']);

        $this->assertDatabaseCount('portal_users', 1);
        Mail::assertNothingSent();
    }

    public function test_registration_response_is_indistinguishable_for_new_and_existing_emails(): void
    {
        Mail::fake();

        $grado = GradoEscolar::factory()->create();
        PortalUser::factory()->create(['email' => 'existente@example.com', 'grado_escolar_id' => $grado->id]);

        $newResponse = $this->post(route('portal.register.attempt'), [
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo@example.com',
            'alumno_nombre' => 'Alumno Nuevo',
            'grado_escolar_id' => $grado->id,
        ]);

        $existingResponse = $this->post(route('portal.register.attempt'), [
            'name' => 'Usuario Existente',
            'email' => 'existente@example.com',
            'alumno_nombre' => 'Alumno Existente',
            'grado_escolar_id' => $grado->id,
        ]);

        $newResponse->assertRedirect(route('portal.login'));
        $existingResponse->assertRedirect(route('portal.login'));

        $this->assertSame(
            $newResponse->headers->get('Location'),
            $existingResponse->headers->get('Location'),
        );
        $this->assertSame(
            $newResponse->getSession()->get('status'),
            $existingResponse->getSession()->get('status'),
        );
    }

    public function test_login_fails_without_leaking_user_existence(): void
    {
        $grado = GradoEscolar::factory()->create();
        PortalUser::factory()->create([
            'email' => 'juan@example.com',
            'password' => 'correct-password',
            'grado_escolar_id' => $grado->id,
            'must_change_password' => false,
        ]);

        $this->post(route('portal.login.attempt'), [
            'email' => 'juan@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors(['email']);

        $this->post(route('portal.login.attempt'), [
            'email' => 'no-existe@example.com',
            'password' => 'any-password',
        ])->assertSessionHasErrors(['email']);
    }

    public function test_first_login_redirects_to_password_change(): void
    {
        $grado = GradoEscolar::factory()->create();
        $portalUser = PortalUser::factory()->create([
            'grado_escolar_id' => $grado->id,
            'must_change_password' => true,
        ]);

        $this->post(route('portal.login.attempt'), [
            'email' => $portalUser->email,
            'password' => 'password',
        ])->assertRedirect(route('portal.dashboard'));

        $this->actingAs($portalUser, 'portal')
            ->get(route('portal.dashboard'))
            ->assertRedirect(route('portal.password.change'));
    }

    public function test_portal_user_can_change_temporary_password(): void
    {
        $grado = GradoEscolar::factory()->create();
        $portalUser = PortalUser::factory()->create([
            'grado_escolar_id' => $grado->id,
            'must_change_password' => true,
        ]);

        $this->actingAs($portalUser, 'portal')
            ->get(route('portal.password.change'))
            ->assertOk();

        $this->actingAs($portalUser, 'portal')
            ->post(route('portal.password.change.update'), [
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])->assertRedirect(route('portal.dashboard'));

        $portalUser->refresh();
        $this->assertFalse($portalUser->must_change_password);
        $this->assertTrue(Hash::check('new-secure-password', $portalUser->password));

        $this->post(route('portal.logout'))->assertRedirect(route('portal.login'));

        $this->post(route('portal.login.attempt'), [
            'email' => $portalUser->email,
            'password' => 'new-secure-password',
        ])->assertRedirect(route('portal.dashboard'));
    }

    public function test_password_change_requires_matching_confirmation(): void
    {
        $grado = GradoEscolar::factory()->create();
        $portalUser = PortalUser::factory()->create([
            'grado_escolar_id' => $grado->id,
            'must_change_password' => true,
        ]);

        $this->actingAs($portalUser, 'portal')
            ->post(route('portal.password.change.update'), [
                'password' => 'new-secure-password',
                'password_confirmation' => 'different-password',
            ])->assertSessionHasErrors(['password']);

        $this->actingAs($portalUser, 'portal')
            ->post(route('portal.password.change.update'), [
                'password' => 'short',
                'password_confirmation' => 'short',
            ])->assertSessionHasErrors(['password']);

        $portalUser->refresh();
        $this->assertTrue($portalUser->must_change_password);
    }

    public function test_changed_password_user_cannot_access_change_form(): void
    {
        $grado = GradoEscolar::factory()->create();
        $portalUser = PortalUser::factory()->create([
            'grado_escolar_id' => $grado->id,
            'must_change_password' => false,
        ]);

        $this->actingAs($portalUser, 'portal')
            ->get(route('portal.password.change'))
            ->assertRedirect(route('portal.dashboard'));
    }

    public function test_guests_cannot_access_portal_dashboard(): void
    {
        $this->get(route('portal.dashboard'))->assertRedirect(route('portal.login'));
    }
}
