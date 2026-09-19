@php

    $children = $children ?? [];
    $type = $field['type'] ?? 'text';
    $optionAttributes = $optionAttributes ?? [];

    $visibilitySource = $field['visibility_field'] ?? null;
    $visibilitySectionTypes = array_values(array_filter((array) ($field['show_for_section_types'] ?? [])));
    $visibilityLayoutTypes = array_values(array_filter((array) ($field['show_for_layout_types'] ?? [])));
    $hasVisibility = filled($visibilitySource) && ($visibilitySectionTypes !== [] || $visibilityLayoutTypes !== []);

    $visibilityAttributes = $hasVisibility
        ? [
            'data-visibility-source' => $visibilitySource,
            'data-visibility-section-types' => implode(',', $visibilitySectionTypes),
            'data-visibility-layout-types' => implode(',', $visibilityLayoutTypes),
            'style' => 'display:none;',
        ]
        : [];

    $customView = $fieldViews->resolve($type);
@endphp

@if($type === 'section_heading')
    <div class="col-12 {{ $hasVisibility ? 'admin-conditional-field' : '' }}" @foreach($visibilityAttributes as $attribute => $attributeValue) {{ $attribute }}="{{ $attributeValue }}" @endforeach>
        <div class="admin-form-section-heading border rounded px-3 py-2 mt-2 fw-bold text-dark" style="background-color:#f3f6fb;border-color:#dbe3ef !important;color:#1f2937 !important;">{{ $field['label'] }}</div>
    </div>

@elseif($type === 'product_bottom_details')
    @include('admin.shared.fields.group', ['field' => $field, 'children' => $children])

@elseif($customView)
    @php
        $customRules = array_map(fn ($rule) => is_string($rule) ? $rule : '', (array) ($field['rules'] ?? []));
        $customIsRequired = collect($customRules)->contains(fn (string $rule): bool => str_starts_with($rule, 'required'))
            || (bool) ($field['force_required_indicator'] ?? false);
    @endphp
    @if($customView['wrap'])
        <div class="col-12">
            @if($customIsRequired && ! empty($field['label']))
                <label class="form-label">
                    {{ $field['label'] }}
                    <span class="text-danger" title="Required field">*</span>
                </label>
            @endif
            @include($customView['view'])
            @if(! empty($field['name']))
                @error($field['name'])<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            @endif
        </div>
    @else
        @include($customView['view'])
    @endif

@elseif(! empty($field['name']))
    @php
        $name = $field['name'];
        $value = old($name, $formData[$name] ?? ($field['default'] ?? null));
        $rulesList = array_map(fn ($rule) => is_string($rule) ? $rule : '', (array) ($field['rules'] ?? []));
        $isRequired = collect($rulesList)->contains(fn (string $rule): bool => str_starts_with($rule, 'required'))
            || (bool) ($field['force_required_indicator'] ?? false);
        if ($type === 'password') {
            // Editing never forces a new password; blank keeps the current one.
            $isRequired = $isRequired && empty($record);
            $field['help'] = ! empty($record) ? 'Leave blank to keep the current password.' : 'Minimum 8 characters.';
        }
        $lockedBySubmenu = in_array($module['key'] ?? '', ['storefront-banners', 'storefront-sections'], true)
            && in_array($name, ['placement', 'section_key'], true)
            && request()->filled($name);
    @endphp

    @if($type === 'hidden' || $lockedBySubmenu)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @else
        <div class="{{ $field['col'] ?? 'col-md-6' }} {{ $hasVisibility ? 'admin-conditional-field' : '' }}" @foreach($visibilityAttributes as $attribute => $attributeValue) {{ $attribute }}="{{ $attributeValue }}" @endforeach>
            @if($type === 'checkbox')
                <div class="form-check form-switch mt-4">
                    <input type="checkbox" class="form-check-input" name="{{ $name }}" value="1" id="{{ $name }}" @checked((bool) $value)>
                    <label class="form-check-label" for="{{ $name }}">{{ $field['label'] }}@if($isRequired) <span class="text-danger" title="Required field">*</span>@endif</label>
                    @error($name)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @if(! empty($field['help']))<small class="text-muted d-block mt-1">{{ $field['help'] }}</small>@endif
                </div>
            @else
                <label class="form-label">
                    {{ $field['label'] }}
                    @if($isRequired)
                        <span class="text-danger" title="Required field">*</span>
                    @endif
                </label>

                @if($type === 'select')
                    <select class="form-select @error($name)is-invalid @enderror" name="{{ $name }}" id="{{ $name }}" data-option-attributes='@json($optionAttributes[$name] ?? [])' @required($isRequired)>
                        <option value="">Select {{ $field['label'] }}</option>
                        @foreach($options[$name] ?? [] as $key => $label)
                            @php($attrs = $optionAttributes[$name][$key] ?? [])
                            <option value="{{ $key }}" @selected((string) $value === (string) $key)
                                @foreach($attrs as $attrName => $attrValue)
                                    @if($attrValue !== null && $attrValue !== '')
                                        data-{{ \Illuminate\Support\Str::kebab($attrName) }}="{{ $attrValue }}"
                                    @endif
                                @endforeach>{{ $label }}</option>
                        @endforeach
                    </select>
                @elseif($type === 'radio')
                    <div class="d-flex flex-wrap gap-3 pt-1">
                        @foreach($field['options'] ?? [] as $key => $label)
                            <div class="form-check">
                                <input class="form-check-input @error($name)is-invalid @enderror" type="radio" name="{{ $name }}" id="{{ $name }}-{{ $key }}" value="{{ $key }}" @checked((string) $value === (string) $key)>
                                <label class="form-check-label" for="{{ $name }}-{{ $key }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                @elseif($type === 'textarea')
                    <textarea rows="{{ $field['rows'] ?? 4 }}" class="form-control @error($name)is-invalid @enderror" name="{{ $name }}" @if(! empty($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif @if(! empty($field['character_counter'])) data-character-counter @endif @required($isRequired)>{{ $value }}</textarea>
                    @if(! empty($field['character_counter']))<small class="text-muted d-block"><span data-character-count>0</span> / {{ $field['maxlength'] ?? 160 }}</small>@endif
                @elseif($type === 'image_multiple')
                    <input class="form-control @error($name)is-invalid @enderror" type="file" name="{{ $name }}[]" accept="image/*" multiple @required($isRequired && ! $record)>

                    @if(($module['key'] ?? '') === 'products')
                        @include('admin.products.partials.images.gallery-preview')
                    @endif
                @elseif(in_array($type, ['file', 'image'], true))
                    <input class="form-control @error($name)is-invalid @enderror" type="file" name="{{ $name }}" accept="{{ $field['accept'] ?? ($type === 'image' ? 'image/*' : '') }}" @required($isRequired && ! $record)>

                    @if($value)
                        @if(($module['key'] ?? '') === 'products')
                            @include('admin.products.partials.images.current-preview')
                        @else
                            @php($isImageFile = in_array(strtolower(pathinfo((string) $value, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'bmp'], true))
                            <div class="admin-gallery-preview-list d-flex flex-wrap gap-2 mt-2">
                                <div class="admin-gallery-preview-item">
                                    <a href="{{ asset($value) }}" target="_blank">
                                        @if($isImageFile)
                                            <img src="{{ asset($value) }}" class="admin-gallery-preview-thumb" alt="Current image">
                                        @else
                                            {{ basename((string) $value) }}
                                        @endif
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endif
                @else
                    <input class="form-control @error($name)is-invalid @enderror" type="{{ $type }}" name="{{ $name }}" value="{{ $type === 'password' ? '' : $value }}" @required($isRequired) step="{{ $field['step'] ?? null }}" placeholder="{{ $field['placeholder'] ?? '' }}">
                @endif

                @error($name)<div class="{{ $type === 'radio' ? 'text-danger small mt-1' : 'invalid-feedback' }}">{{ $message }}</div>@enderror
                @if(! empty($field['help']))<small class="text-muted">{{ $field['help'] }}</small>@endif
            @endif
        </div>
        @if($type === 'password')
            <div class="{{ $field['col'] ?? 'col-md-6' }}">
                <label class="form-label">
                    Confirm {{ $field['label'] }}
                    @if($isRequired)<span class="text-danger" title="Required field">*</span>@endif
                </label>
                <input class="form-control" type="password" name="{{ $name }}_confirmation" value="" autocomplete="new-password" @required($isRequired)>
                <small class="text-muted">Type the same password again.</small>
            </div>
        @endif
    @endif
@endif
