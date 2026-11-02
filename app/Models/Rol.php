<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'slug',
    ];

    /**
     * A freshly created role starts from the approved baseline for its slug so
     * new roles are usable immediately. Unknown slugs simply start all-false.
     */
    protected static function booted(): void
    {
        static::created(function (Rol $rol): void {
            $rol->sembrarPermisosIniciales();
        });
    }

    /**
     * The super-admin role is the anchor of the authorization bypass. Its slug
     * must never change and it must never be deleted: either one would leave
     * the system with no account able to administer permissions.
     */
    public function esProtegido(): bool
    {
        return $this->slug === 'super-admin';
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function permisos(): HasMany
    {
        return $this->hasMany(Permiso::class, 'rol_id');
    }

    /**
     * Modules this role can read, already ordered for the sidebar.
     *
     * @return Collection<int, Modulo>
     */
    public function modulosVisibles(): Collection
    {
        return Modulo::query()
            ->whereHas('permisos', function ($query): void {
                $query->where('rol_id', $this->id)->where('puede_ver', true);
            })
            ->ordenado()
            ->get();
    }

    public function permisoPara(string $clave): ?Permiso
    {
        if ($this->relationLoaded('permisos')) {
            return $this->permisos->first(
                fn (Permiso $permiso): bool => $permiso->modulo?->clave === $clave,
            );
        }

        return $this->permisos()
            ->whereHas('modulo', fn ($query) => $query->where('clave', $clave))
            ->first();
    }

    /**
     * Persist the baseline matrix from `config('modulos.inicial')`. The
     * super-admin role is skipped: it bypasses the matrix in code.
     */
    public function sembrarPermisosIniciales(): void
    {
        if ($this->esProtegido() || ! Schema::hasTable('modulos')) {
            return;
        }

        $modulos = Modulo::query()->pluck('id', 'clave')->all();

        if ($modulos === []) {
            return;
        }

        $now = now();
        $filas = [];

        foreach ($modulos as $clave => $moduloId) {
            $acciones = config('modulos.inicial.'.$clave, []);

            $filas[] = [
                'rol_id' => $this->id,
                'modulo_id' => $moduloId,
                'puede_ver' => in_array($this->slug, $acciones['ver'] ?? [], true),
                'puede_escribir' => in_array($this->slug, $acciones['escribir'] ?? [], true),
                'puede_eliminar' => in_array($this->slug, $acciones['eliminar'] ?? [], true),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Permiso::query()->insertOrIgnore($filas);
    }

    public function scopeSearch($query, ?string $busqueda)
    {
        if ($busqueda === null || trim($busqueda) === '') {
            return $query;
        }

        $like = '%'.trim($busqueda).'%';

        return $query->where(function ($q) use ($like) {
            $q->where('nombre', 'like', $like)
                ->orWhere('slug', 'like', $like);
        });
    }
}
