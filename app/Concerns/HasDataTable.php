<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HasDataTable
{
    protected function applyDataTableFilters(Builder $query, Request $request, array $searchable = [], array $sortable = []): Builder
    {
        $search = $request->input('search', '');
        $sortBy = $request->input('sort_by', 'id');
        $sortDirection = $request->input('sort_direction', 'asc');

        if ($search && count($searchable) > 0) {
            $query->where(function ($q) use ($search, $searchable) {
                foreach ($searchable as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        if (in_array($sortBy, $sortable)) {
            $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';
            $query->orderBy($sortBy, $sortDirection);
        }

        return $query;
    }

    protected function getDataTableResponse($paginator, Request $request): array
    {
        $sortBy = $request->input('sort_by', 'id');
        $sortDirection = $request->input('sort_direction', 'asc');

        return [
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'filters' => [
                'search' => $request->input('search', ''),
                'sort' => $sortBy !== 'id' || $sortDirection !== 'asc' ? [
                    'column' => $sortBy,
                    'direction' => $sortDirection,
                ] : null,
                'per_page' => (int) $request->input('per_page', 10),
                'page' => $paginator->currentPage(),
            ],
        ];
    }
}
