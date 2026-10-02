<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortalPasswordChangeRequired
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('portal')->user();

        if ($user && ! $user->must_change_password) {
            return redirect()->route('portal.dashboard');
        }

        return $next($request);
    }
}
