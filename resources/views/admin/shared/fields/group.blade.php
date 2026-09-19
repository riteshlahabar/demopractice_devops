@php

    $children = collect($children ?? [])->filter(fn (array $child): bool => $fieldTree->shouldRender($child, (bool) $record));
    $groups = $fieldTree->groups($field);
    $ungrouped = $children->reject(fn (array $child): bool => isset($groups[$child['render_group'] ?? '']));
@endphp

<div class="col-12">
    @foreach($groups as $groupKey => $groupLabel)
        @php($groupChildren = $children->filter(fn (array $child): bool => ($child['render_group'] ?? null) === $groupKey))
        @continue($groupChildren->isEmpty())

        <div class="border rounded p-3 mb-3">
            <h6 class="fw-bold text-dark mb-3">{{ $groupLabel }}</h6>
            <div class="row g-3">
                @foreach($groupChildren as $child)
                    @include('admin.shared.fields.field', ['field' => $child, 'children' => []])
                @endforeach
            </div>
        </div>
    @endforeach

    @if($ungrouped->isNotEmpty())
        <div class="row g-3">
            @foreach($ungrouped as $child)
                @include('admin.shared.fields.field', ['field' => $child, 'children' => []])
            @endforeach
        </div>
    @endif
</div>
