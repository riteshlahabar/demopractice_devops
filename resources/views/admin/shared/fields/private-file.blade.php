<label class="form-label">{{ $field['label'] }}</label>
<input class="form-control @error($field['name']) is-invalid @enderror" type="file" name="{{ $field['name'] }}" accept="{{ $field['accept'] ?? '' }}">
@if($record && ! empty($record->{$field['name']}))
    <small class="d-block mt-1">
        <a href="{{ route($module['route'].'.download', $record->getKey()) }}"><i class="iconoir-download me-1"></i>Download current file</a>
        <span class="text-muted">— upload a new file to replace it.</span>
    </small>
@endif
@if(! empty($field['help']))<small class="text-muted d-block">{{ $field['help'] }}</small>@endif
