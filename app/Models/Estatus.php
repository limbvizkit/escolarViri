<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estatus extends Model
{
    protected $table = 'estatus';

    public const ACTIVO = 1;

    public const INACTIVO = 2;

    public const ELIMINADO = 3;

    protected $fillable = ['nombre', 'slug'];
}
