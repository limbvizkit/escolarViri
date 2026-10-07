<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CursoAlumno extends Model
{
    use HasFactory;

    protected $table = 'curso_alumno';

    protected $fillable = [
        'curso_id',
        'alumno_id',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }
}
