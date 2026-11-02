<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'empleado_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Memoized role matrix keyed by module clave.
     *
     * @var array<string, Permiso>|null
     */
    private ?array $permisosPorClave = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Normalize the email to lowercase before persisting it, so accounts that
     * only differ by letter case cannot coexist.
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => $value === null ? null : strtolower(trim($value)),
        );
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'role_id');
    }

    /**
     * Super-admin is an invariant, not a matrix row: it always bypasses.
     */
    public function esSuperAdmin(): bool
    {
        return $this->rol?->slug === 'super-admin';
    }

    public function puedeVer(string $clave): bool
    {
        return $this->puede($clave, 'puede_ver');
    }

    public function puedeEscribir(string $clave): bool
    {
        return $this->puede($clave, 'puede_escribir');
    }

    public function puedeEliminar(string $clave): bool
    {
        return $this->puede($clave, 'puede_eliminar');
    }

    /**
     * Modules this user can read, ordered for the sidebar.
     *
     * @return Collection<int, Modulo>
     */
    public function modulosVisibles()
    {
        if ($this->esSuperAdmin()) {
            return Modulo::query()->ordenado()->get();
        }

        return $this->rol?->modulosVisibles() ?? new Collection;
    }

    private function puede(string $clave, string $atributo): bool
    {
        if ($this->esSuperAdmin()) {
            return true;
        }

        $permiso = $this->permisosPorClave()[$clave] ?? null;

        return (bool) ($permiso?->{$atributo} ?? false);
    }

    /**
     * Load the role matrix once per instance so 22 sidebar checks do not fire
     * 22 queries.
     *
     * @return array<string, Permiso>
     */
    private function permisosPorClave(): array
    {
        if ($this->permisosPorClave !== null) {
            return $this->permisosPorClave;
        }

        $this->permisosPorClave = [];

        $rol = $this->rol;

        if ($rol === null) {
            return $this->permisosPorClave;
        }

        if (! $rol->relationLoaded('permisos')) {
            $rol->load('permisos.modulo');
        }

        foreach ($rol->permisos as $permiso) {
            $clave = $permiso->modulo?->clave;

            if ($clave !== null) {
                $this->permisosPorClave[$clave] = $permiso;
            }
        }

        return $this->permisosPorClave;
    }

    public function scopeSearch($query, ?string $busqueda)
    {
        if ($busqueda === null || trim($busqueda) === '') {
            return $query;
        }

        $like = '%'.trim($busqueda).'%';

        return $query->where(function ($q) use ($like) {
            $q->where('name', 'like', $like)
                ->orWhere('email', 'like', $like);
        });
    }
}
