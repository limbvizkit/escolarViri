<?php

namespace App\Http\Controllers;

/**
 * Módulo de Estimulación Temprana.
 *
 * Reutiliza toda la lógica del módulo de Alumnos, pero limitado al grado
 * escolar "Estimulación Temprana": solo lista y da de alta alumnos de ese
 * grado, y fuerza ese grado en el alta/edición.
 */
class EstimulacionTempranaController extends AlumnoController
{
    protected function esEstimulacionTemprana(): bool
    {
        return true;
    }

    protected function prefijoRuta(): string
    {
        return 'estimulacion-temprana';
    }

    protected function tituloModulo(): string
    {
        return 'Estimulación Temprana';
    }
}
