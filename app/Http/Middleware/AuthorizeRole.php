<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();

        if ($user === null || $user->rol === null) {
            abort(403, 'Forbidden');
        }

        $allowed = array_map('trim', explode('|', $roles));

        if (! in_array($user->rol->slug, $allowed, true)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
