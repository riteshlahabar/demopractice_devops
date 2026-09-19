@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
@php
    // $fieldTree, $fieldViews and $fieldNodes are supplied by the view composer.
    $hasUpload = collect($module['fields'] ?? [])->contains(fn ($field) => in_array($field['type'] ?? '', ['file', 'private_file', 'image', 'image_multiple', 'product_media_repeater'], true));
    $submenuQueryKeys = ['type', 'placement', 'section_key', 'row_title'];
    $fieldNames = collect($module['fields'] ?? [])->pluck('name')->filter()->values()->all();
    $optionAttributes = $optionAttributes ?? [];

    // Modules that opt in with 'form_layout' => 'tabs' render one tab per
    // section_heading instead of one long column. Nothing else changes: it is
    // still a single <form> with a single submit, so validation, the field
    // partials and the controllers are untouched.
    $useTabs = ($module['form_layout'] ?? null) === 'tabs';
    $tabGroups = [];

    if ($useTabs) {
        $currentGroup = null;

        foreach ($fieldNodes as $node) {
            if (! $fieldTree->shouldRender($node['field'], (bool) $record)) {
                continue;
            }

            if (($node['field']['type'] ?? '') === 'section_heading') {
                $tabGroups[] = ['heading' => $node['field'], 'nodes' => []];
                $currentGroup = array_key_last($tabGroups);

                continue;
            }

            if ($currentGroup === null) {
                $tabGroups[] = ['heading' => ['label' => 'General'], 'nodes' => []];
                $currentGroup = array_key_last($tabGroups);
            }

            $tabGroups[$currentGroup]['nodes'][] = $node;
        }

        // A heading with no visible fields left would render an empty tab.
        $tabGroups = array_values(array_filter($tabGroups, fn (array $group): bool => $group['nodes'] !== []));

        // Count the errors landing in each tab so the user can see where to go.
        // A dotted key such as "variants.0.mrp" belongs to the "variants" field.
        $errorKeys = collect($errors->keys());

        foreach ($tabGroups as $groupIndex => $group) {
            $tabGroups[$groupIndex]['error_count'] = collect($group['nodes'])
                ->sum(function (array $node) use ($errorKeys): int {
                    $name = $node['field']['name'] ?? null;

                    if (blank($name)) {
                        return 0;
                    }

                    return $errorKeys
                        ->filter(fn (string $key): bool => $key === $name || str_starts_with($key, $name.'.'))
                        ->count();
                });
        }

        $activeTab = collect($tabGroups)->search(fn (array $group): bool => ($group['error_count'] ?? 0) > 0);
        $activeTab = $activeTab === false ? 0 : $activeTab;
        $useTabs = count($tabGroups) > 1;
    }
@endphp
<div class="row admin-form-row">
    <div class="col-12">
        <div class="card admin-form-card">
            <div class="card-body pt-3">
                {{-- The admin layout already renders the validation summary for
                     every page, so this form must not repeat it. --}}

                <p class="admin-form-required-legend text-muted small mb-3">
                    Fields marked <span class="text-danger">*</span> are compulsory.
                </p>

                <form method="POST" action="{{ $record ? route($module['route'].'.update', array_merge([$record->getKey()], request()->only($submenuQueryKeys))) : route($module['route'].'.store', request()->only($submenuQueryKeys)) }}" @if($hasUpload) enctype="multipart/form-data" @endif>
                    @csrf
                    @if($record) @method('PUT') @endif

                    @foreach(request()->only($submenuQueryKeys) as $queryKey => $queryValue)
                        @if(is_scalar($queryValue) && ! in_array($queryKey, $fieldNames, true))
                            <input type="hidden" name="{{ $queryKey }}" value="{{ $queryValue }}">
                        @endif
                    @endforeach

                    @if($useTabs)
                        <ul class="nav nav-tabs admin-form-tabs" role="tablist">
                            @foreach($tabGroups as $tabIndex => $group)
                                @php
                                    $heading = $group['heading'];
                                    $paneId = 'formTab'.$module['key'].$tabIndex;
                                    $tabLabel = preg_replace('/^\s*\d+\.\s*/', '', (string) ($heading['label'] ?? 'Section'));
                                    $tabVisibilitySource = $heading['visibility_field'] ?? null;
                                    $tabSectionTypes = array_values(array_filter((array) ($heading['show_for_section_types'] ?? [])));
                                    $tabLayoutTypes = array_values(array_filter((array) ($heading['show_for_layout_types'] ?? [])));
                                    $tabHasVisibility = filled($tabVisibilitySource) && ($tabSectionTypes !== [] || $tabLayoutTypes !== []);
                                @endphp
                                <li class="nav-item {{ $tabHasVisibility ? 'admin-conditional-field' : '' }}" role="presentation"
                                    @if($tabHasVisibility)
                                        data-visibility-source="{{ $tabVisibilitySource }}"
                                        data-visibility-section-types="{{ implode(',', $tabSectionTypes) }}"
                                        data-visibility-layout-types="{{ implode(',', $tabLayoutTypes) }}"
                                        style="display:none;"
                                    @endif>
                                    <button class="nav-link {{ $tabIndex === $activeTab ? 'active' : '' }}" id="{{ $paneId }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $paneId }}" type="button" role="tab" aria-controls="{{ $paneId }}" aria-selected="{{ $tabIndex === $activeTab ? 'true' : 'false' }}">
                                        <span class="admin-form-tab-index">{{ $tabIndex + 1 }}</span>
                                        <span class="admin-form-tab-label">{{ $tabLabel }}</span>
                                        @if(($group['error_count'] ?? 0) > 0)
                                            <span class="badge bg-danger admin-form-tab-errors">{{ $group['error_count'] }}</span>
                                        @endif
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content admin-form-tab-content">
                            @foreach($tabGroups as $tabIndex => $group)
                                <div class="tab-pane fade {{ $tabIndex === $activeTab ? 'show active' : '' }}" id="{{ 'formTab'.$module['key'].$tabIndex }}" role="tabpanel" aria-labelledby="{{ 'formTab'.$module['key'].$tabIndex }}-tab">
                                    <div class="row g-3">
                                        @foreach($group['nodes'] as $node)
                                            @include('admin.shared.fields.field', ['field' => $node['field'], 'children' => $node['children']])
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($fieldNodes as $node)
                                @continue(! $fieldTree->shouldRender($node['field'], (bool) $record))
                                @include('admin.shared.fields.field', ['field' => $node['field'], 'children' => $node['children']])
                            @endforeach
                        </div>
                    @endif

                    <div class="d-flex justify-content-end gap-2 mt-4 admin-form-actions">
                        <a class="btn btn-outline-secondary" href="{{ route($module['route'].'.index', request()->only(['type','placement','section_key','row_title'])) }}">Cancel</a>
                        <button class="btn btn-primary" type="submit"><i class="iconoir-check-circle me-1"></i>{{ $record ? 'Update' : 'Save' }} {{ $module['singular'] }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    function syncConditionalFields(form) {
        var conditionalBlocks = form.querySelectorAll('.admin-conditional-field[data-visibility-source]');
        if (!conditionalBlocks.length) {
            return;
        }

        conditionalBlocks.forEach(function (block) {
            var sourceName = block.dataset.visibilitySource;
            var source = form.elements.namedItem(sourceName);
            if (!source || !('options' in source)) {
                return;
            }

            var selectedOption = source.options[source.selectedIndex] || null;
            var selectedValue = source.value || '';
            var optionAttributeMap = {};

            if (source.dataset.optionAttributes) {
                try {
                    optionAttributeMap = JSON.parse(source.dataset.optionAttributes);
                } catch (error) {
                    optionAttributeMap = {};
                }
            }

            var selectedOptionAttributes = selectedValue && optionAttributeMap[selectedValue] ? optionAttributeMap[selectedValue] : {};
            var sectionType = selectedOption ? (selectedOption.dataset.sectionType || '') : '';
            var layoutType = selectedOption ? (selectedOption.dataset.layoutType || '') : '';

            if (!sectionType && selectedOptionAttributes.section_type) {
                sectionType = selectedOptionAttributes.section_type;
            }

            if (!layoutType && selectedOptionAttributes.layout_type) {
                layoutType = selectedOptionAttributes.layout_type;
            }

            var allowedSectionTypes = (block.dataset.visibilitySectionTypes || '').split(',').map(function (value) {
                return value.trim();
            }).filter(Boolean);
            var allowedLayoutTypes = (block.dataset.visibilityLayoutTypes || '').split(',').map(function (value) {
                return value.trim();
            }).filter(Boolean);
            var isVisible = Boolean(selectedValue);

            if (isVisible && allowedSectionTypes.length) {
                isVisible = allowedSectionTypes.indexOf(sectionType) !== -1;
            }

            if (isVisible && allowedLayoutTypes.length) {
                isVisible = allowedLayoutTypes.indexOf(layoutType) !== -1;
            }

            block.style.display = isVisible ? '' : 'none';

            block.querySelectorAll('input, select, textarea').forEach(function (control) {
                if (!control.dataset.conditionalOriginalDisabled) {
                    control.dataset.conditionalOriginalDisabled = control.disabled ? '1' : '0';
                }

                control.disabled = isVisible ? control.dataset.conditionalOriginalDisabled === '1' : true;
            });
        });

        // A tab strip item can be conditional too, so the tab layout needs a
        // chance to move off a tab that just became hidden.
        if (typeof window.adminFormTabsSync === 'function') {
            window.adminFormTabsSync(form);
        }
    }


    function initCharacterCounters() {
        document.querySelectorAll('[data-character-counter]').forEach(function (field) {
            var target = field.parentElement.querySelector('[data-character-count]');
            var sync = function () {
                if (target) target.textContent = String(field.value.length);
            };
            field.addEventListener('input', sync);
            sync();
        });
    }
    function initConditionalAdminFields() {
        document.querySelectorAll('.admin-form-card form').forEach(function (form) {
            var conditionalBlocks = form.querySelectorAll('.admin-conditional-field[data-visibility-source]');
            if (!conditionalBlocks.length) {
                return;
            }

            Array.from(new Set(Array.from(conditionalBlocks).map(function (block) {
                return block.dataset.visibilitySource;
            }))).forEach(function (sourceName) {
                var source = form.elements.namedItem(sourceName);
                if (source) {
                    source.addEventListener('change', function () {
                        syncConditionalFields(form);
                    });
                }
            });

            syncConditionalFields(form);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initConditionalAdminFields();
            initCharacterCounters();
        });
    } else {
        initConditionalAdminFields();
        initCharacterCounters();
    }
})();
</script>

@if(($module['key'] ?? '') === 'products')
    @include('admin.products.partials.scripts')
@endif
@endsection
@push('scripts')
    <script src="{{ asset('admin-module-js/shared/form-tabs.js') }}"></script>
@endpush




