@props([
    'field',
    'label',
    'current' => null,
    'direction' => 'asc',
])

@php
    $query = request()->query();

    if (($current === $field) && ($direction === 'asc')) {
        $nextDirection = 'desc';
    } else {
        $nextDirection = 'asc';
    }

    $query['sort'] = $field;
    $query['direction'] = $nextDirection;

    $href = url()->current() . '?' . http_build_query($query);
    $active = $current === $field;
@endphp

<th class="ip-th-sortable">
    <a href="{{ $href }}"
       class="ip-sort {{ $active ? 'active' : '' }} {{ $active ? $nextDirection === 'asc' ? 'desc' : 'asc' : '' }}"
       @if ($active) aria-current="true" @endif>
        <span>{{ $label }}</span>
        <span class="ip-sort-icons">
            <i class="bi bi-caret-up-fill ip-sort-up"></i>
            <i class="bi bi-caret-down-fill ip-sort-down"></i>
        </span>
    </a>
</th>