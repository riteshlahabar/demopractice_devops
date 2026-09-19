@php
    $row = (array) ($row ?? []);
    $index = $index ?? '__INDEX__';
    $sourceType = $row['source_type'] ?? 'upload';
    $isActive = ! array_key_exists('is_active', $row) || filter_var($row['is_active'], FILTER_VALIDATE_BOOL);
@endphp
<div class="border rounded p-3 mb-3 bg-light" data-product-media-row>
    <input type="hidden" name="media[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}">
    <input type="hidden" name="media[{{ $index }}][existing_file_path]" value="{{ $row['file_path'] ?? '' }}">
    <input type="hidden" name="media[{{ $index }}][existing_thumbnail_path]" value="{{ $row['thumbnail_path'] ?? '' }}">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>Product Video</strong>
        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-repeater-row><i class="iconoir-trash"></i> Remove</button>
    </div>
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Video Source <span class="text-danger">*</span></label>
            <select class="form-select" name="media[{{ $index }}][source_type]" data-media-source required>
                <option value="upload" @selected($sourceType === 'upload')>Upload MP4 / WebM</option>
                <option value="youtube" @selected($sourceType === 'youtube')>YouTube URL</option>
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label">Video Title</label>
            <input class="form-control" type="text" maxlength="255" name="media[{{ $index }}][title]" value="{{ $row['title'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Language</label>
            <input class="form-control" type="text" maxlength="10" placeholder="en / mr" name="media[{{ $index }}][language]" value="{{ $row['language'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Sort Order</label>
            <input class="form-control" type="number" min="0" name="media[{{ $index }}][sort_order]" value="{{ $row['sort_order'] ?? 0 }}">
        </div>
        <div class="col-md-6" data-media-upload-field>
            <label class="form-label">Video File</label>
            <input class="form-control" type="file" name="media[{{ $index }}][file]" accept="video/mp4,video/webm">
            @if(! empty($row['file_path']))<small class="text-muted">Current: {{ basename($row['file_path']) }}</small>@endif
        </div>
        <div class="col-md-6" data-media-youtube-field>
            <label class="form-label">YouTube URL</label>
            <input class="form-control" type="url" name="media[{{ $index }}][youtube_url]" value="{{ $row['youtube_url'] ?? '' }}" placeholder="https://www.youtube.com/watch?v=...">
        </div>
        <div class="col-md-6">
            <label class="form-label">Gallery Thumbnail (Optional)</label>
            <input class="form-control" type="file" name="media[{{ $index }}][thumbnail]" accept="image/*">
            @if(! empty($row['thumbnail_path']))<a href="{{ asset($row['thumbnail_path']) }}" target="_blank" class="small d-block mt-1">View current thumbnail</a>@endif
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <input type="hidden" name="media[{{ $index }}][is_active]" value="0">
            <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" name="media[{{ $index }}][is_active]" value="1" id="media-active-{{ $index }}" @checked($isActive)>
                <label class="form-check-label" for="media-active-{{ $index }}">Active</label>
            </div>
        </div>
    </div>
</div>
