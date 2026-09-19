@php
    $listName = $field['name'];
    $checked = array_map('strval', (array) old($listName, $formData[$listName] ?? ($field['default'] ?? [])));
@endphp
<label class="form-label">{{ $field['label'] }}</label>
<div class="d-flex flex-wrap gap-3">
    @foreach($field['options'] ?? [] as $optionValue => $optionLabel)
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="{{ $listName }}[]" id="{{ $listName }}-{{ $optionValue }}" value="{{ $optionValue }}" @checked(in_array((string) $optionValue, $checked, true))>
            <label class="form-check-label" for="{{ $listName }}-{{ $optionValue }}">{{ $optionLabel }}</label>
        </div>
    @endforeach
</div>
@if(! empty($field['help']))<small class="text-muted d-block">{{ $field['help'] }}</small>@endif
