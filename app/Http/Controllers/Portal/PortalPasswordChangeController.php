<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalPasswordChangeController extends Controller
{
    public function showChangePasswordForm(): View
    {
        return view('portal.passwords.change');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::guard('portal')->user();

        $user->password = $data['password'];
        $user->must_change_password = false;
        $user->save();

        $request->session()->regenerate();

        return redirect()->route('portal.dashboard')->with('success', 'Tu contraseña fue actualizada correctamente.');
    }
}
