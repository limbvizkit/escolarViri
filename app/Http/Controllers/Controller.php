<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Devuelve el campo de orden permitido o el valor por defecto.
     */
    protected function sortField(Request $request, array $allowed, string $default = 'id'): string
    {
        $field = (string) $request->input('sort', $default);

        return in_array($field, $allowed, true) ? $field : $default;
    }

    /**
     * Devuelve la dirección de orden ('asc' o 'desc').
     */
    protected function sortDirection(Request $request): string
    {
        return $request->input('direction') === 'desc' ? 'desc' : 'asc';
    }

    /**
     * Aplica ordenamiento al builder y entrega el paginador con query string.
     */
    protected function paginateOrdered(Builder $query, Request $request, array $allowedSorts, string $defaultSort = 'id')
    {
        $query->orderBy(
            $this->sortField($request, $allowedSorts, $defaultSort),
            $this->sortDirection($request),
        );

        $perPage = min((int) $request->input('per_page', 10) ?: 10, 100);

        return $query->paginate($perPage)->withQueryString();
    }
}
