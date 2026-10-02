<?php

namespace App\Providers;

use App\Models\AcademicDocument;
use App\Models\Adeudo;
use App\Models\Documento;
use App\Models\Pago;
use App\Models\TallerAlumno;
use App\Models\User;
use App\Policies\AcademicDocumentPolicy;
use App\Policies\AdeudoPolicy;
use App\Policies\DocumentoPolicy;
use App\Policies\PagoPolicy;
use App\Policies\TallerAlumnoPolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('login').'|'.$request->ip());
        });

        RateLimiter::for('portal-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email').'|'.$request->ip());
        });

        RateLimiter::for('portal-register', function (Request $request) {
            return [
                Limit::perMinute(3)->by($request->ip()),
                Limit::perMinute(3)->by($request->input('email').'|'.$request->ip()),
            ];
        });

        Paginator::useBootstrapFive();

        Gate::policy(Documento::class, DocumentoPolicy::class);
        Gate::policy(AcademicDocument::class, AcademicDocumentPolicy::class);
        Gate::policy(Pago::class, PagoPolicy::class);
        Gate::policy(Adeudo::class, AdeudoPolicy::class);
        Gate::policy(TallerAlumno::class, TallerAlumnoPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
