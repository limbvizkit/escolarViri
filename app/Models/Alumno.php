<?php

namespace App\Models;

use App\Models\Concerns\ConEstatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alumno extends Model
{
    use ConEstatus;
    use HasFactory;

    public const SEXO_NINO = 'niño';

    public const SEXO_NINA = 'niña';

    public const CONCEPTO_SI = 'SI';

    public const CONCEPTO_NO_APLICA = 'NO APLICA';

    public const CONCEPTO_PENDIENTE = 'PENDIENTE';

    /**
     * Estados posibles para los conceptos anuales/extraordinarios.
     *
     * @var array<int, string>
     */
    public const CONCEPTOS_ESTADO = [
        self::CONCEPTO_SI,
        self::CONCEPTO_NO_APLICA,
        self::CONCEPTO_PENDIENTE,
    ];

    protected $fillable = [
        'grado_escolar_id',
        'sucursal_id',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'sexo',
        'fecha_nacimiento',
        'horario',
        'horario_extendido_id',
        'inscripcion',
        'reinscripcion',
        'entrevista_inicial',
        'nat_geo',
        'cuota_materiales',
        'fecha_ingreso',
        'cuota_mensual',
        'estatus_id',
        'archivo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_ingreso' => 'date',
            'cuota_mensual' => 'decimal:2',
        ];
    }

    public function gradoEscolar(): BelongsTo
    {
        return $this->belongsTo(GradoEscolar::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function horarioExtendido(): BelongsTo
    {
        return $this->belongsTo(HorarioExtendido::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(AlumnoArchivo::class);
    }

    public function academicDocuments(): HasMany
    {
        return $this->hasMany(AcademicDocument::class);
    }

    public function talleres(): BelongsToMany
    {
        return $this->belongsToMany(Taller::class, 'taller_alumno')
            ->withPivot('hora_inicio', 'hora_fin', 'monto_pagado')
            ->withTimestamps();
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre.' '.$this->apellido_paterno.' '.$this->apellido_materno);
    }

    public function getSexoLabelAttribute(): ?string
    {
        if ($this->sexo === null) {
            return null;
        }

        return self::opcionesSexo()[$this->sexo] ?? ucfirst($this->sexo);
    }

    public static function opcionesSexo(): array
    {
        return [
            self::SEXO_NINO => 'Niño',
            self::SEXO_NINA => 'Niña',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function opcionesConcepto(): array
    {
        return [
            self::CONCEPTO_SI => 'SI',
            self::CONCEPTO_NO_APLICA => 'NO APLICA',
            self::CONCEPTO_PENDIENTE => 'PENDIENTE',
        ];
    }

    public function scopeSearch($query, ?string $busqueda)
    {
        if ($busqueda === null || trim($busqueda) === '') {
            return $query;
        }

        $like = '%'.trim($busqueda).'%';

        return $query->where(function ($q) use ($like) {
            $q->where('nombre', 'like', $like)
                ->orWhere('apellido_paterno', 'like', $like)
                ->orWhere('apellido_materno', 'like', $like)
                ->orWhere('horario', 'like', $like);
        });
    }
}
