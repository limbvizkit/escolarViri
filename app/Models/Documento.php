<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Documento extends Model
{
    public const TIPOS = [
        'acta_nacimiento' => 'Acta de Nacimiento',
        'curp' => 'CURP',
        'cartilla_vacunacion' => 'Cartilla Vacunación',
        'carta_pediatra' => 'Carta Pediatra',
        'reglamento' => 'Reglamento',
        'carta_compromiso' => 'Carta Compromiso',
        'entrevista_inicial' => 'Entrevista Inicial',
        'datos_generales' => 'Datos Generales',
    ];

    protected $fillable = [
        'alumno_id',
        'tipo',
        'archivo',
    ];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public static function etiqueta(string $tipo): string
    {
        return self::TIPOS[$tipo] ?? $tipo;
    }
}
