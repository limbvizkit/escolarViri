<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Mail\PortalUserRegistered;
use App\Models\GradoEscolar;
use App\Models\PortalUser;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PortalRegistrationController extends Controller
{
    public function showRegistrationForm(): View
    {
        return view('portal.register', [
            'grados' => GradoEscolar::orderBy('nombre')->pluck('nombre', 'id'),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $email = $request->input('email');

        if (is_string($email)) {
            $request->merge(['email' => strtolower(trim($email))]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'alumno_nombre' => ['required', 'string', 'max:255'],
            'grado_escolar_id' => ['required', 'integer', 'exists:grados_escolares,id'],
        ]);

        if (! PortalUser::where('email', $data['email'])->exists()) {
            try {
                $tempPassword = Str::random(12);

                $portalUser = PortalUser::create([
                    ...$data,
                    'password' => $tempPassword,
                    'must_change_password' => true,
                ]);

                Mail::to($portalUser->email)->send(new PortalUserRegistered($portalUser, $tempPassword));
            } catch (QueryException) {
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('portal.login')->with('status', 'Si el correo es válido, recibirás tus datos de acceso en tu correo.');
    }
}
