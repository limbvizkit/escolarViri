@props([
    'action' => null,
    'filters' => [],
    'placeholder' => 'Buscar...',
])

@php
    $action = $action ?? url()->current();
    $perPage = request()->input('per_page', 10);
    $perPageOptions = [10, 15, 25, 50];
    $activeFilterNames = array_column($filters, 'name');
    $hasActiveFilters = request()->filled('q')
        || count(array_filter(request()->only($activeFilterNames))) > 0;
@endphp

<form method="GET" action="{{ $action }}" class="ip-filters">
    <div class="row g-2 align-items-end">

        <div class="col-12 col-md-4 col-lg-3">
            <label class="form-label ip-filters-label" for="table-q">Buscar</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="table-q" name="q" class="form-control"
                       value="{{ request('q') }}" placeholder="{{ $placeholder }}">
            </div>
        </div>

        @foreach ($filters as $filter)
            <div class="col-6 col-md-auto">
                <label class="form-label ip-filters-label" for="filter-{{ Str::slug($filter['name']) }}">
                    {{ $filter['label'] }}
                </label>
                <select name="{{ $filter['name'] }}" id="filter-{{ Str::slug($filter['name']) }}"
                        class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach ($filter['options'] as $value => $label)
                        <option value="{{ $value }}" @selected(request($filter['name']) == $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach

        <div class="col-6 col-md-auto">
            <label class="form-label ip-filters-label" for="table-per-page">Mostrar</label>
            <select name="per_page" id="table-per-page" class="form-select form-select-sm">
                @foreach ($perPageOptions as $opt)
                    <option value="{{ $opt }}" @selected((int) $perPage === $opt)>{{ $opt }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-auto d-flex gap-2 mt-2 mt-md-0">
            <button type="submit" class="btn ip-btn btn-sm">
                <i class="bi bi-funnel-fill me-1"></i>Filtrar
            </button>
            @if ($hasActiveFilters)
                <a href="{{ url()->current() }}" class="btn ip-btn-outline btn-sm">
                    <i class="bi bi-x-lg me-1"></i>Limpiar
                </a>
            @endif
        </div>

        <input type="hidden" name="sort" value="{{ request('sort') }}">
        <input type="hidden" name="direction" value="{{ request('direction') }}">
    </div>
</form>