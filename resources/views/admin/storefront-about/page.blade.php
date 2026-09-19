@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
@php
    $sections = [
        'Intro Block' => [
            ['intro_label', 'Small Heading', 'text', 'col-md-6'],
            ['intro_heading', 'Main Heading', 'text', 'col-md-6'],
            ['intro_text', 'Intro Text', 'textarea', 'col-12'],
            ['image_one', 'Photo 1', 'file', 'col-md-6'],
            ['image_two', 'Photo 2', 'file', 'col-md-6'],
        ],
        '"What We Do" Block' => [
            ['stats_label', 'Small Heading', 'text', 'col-md-6'],
            ['stats_heading', 'Main Heading', 'text', 'col-md-6'],
        ],
        'Team Block' => [
            ['team_label', 'Small Heading', 'text', 'col-md-6'],
            ['team_heading', 'Main Heading', 'text', 'col-md-6'],
        ],
    ];
    $imageUrl = function (?string $path): ?string {
        return blank($path) ? null : (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path));
    };
    $previews = ['image_one' => $imageUrl($setting->image_one_path), 'image_two' => $imageUrl($setting->image_two_path)];
@endphp
<div class="row admin-form-row">
    <div class="col-12">
        <div class="card admin-form-card">
            <div class="card-body pt-3">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <strong>Please correct the following:</strong>
                        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <p class="text-muted">The bullets under the intro text, the figures in "What We Do" and the people in the team row are managed in <a href="{{ route('admin.storefront-about-items.index') }}">About Sections</a> and <a href="{{ route('admin.storefront-team-members.index') }}">Team Members</a>. A blank heading hides that line on the website.</p>

                <form method="POST" action="{{ route('admin.storefront-about.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        @foreach($sections as $section => $fields)
                            <div class="col-12">
                                <div class="admin-form-section-heading border rounded px-3 py-2 mt-2 fw-bold text-dark" style="background-color:#f3f6fb;border-color:#dbe3ef !important;color:#1f2937 !important;">{{ $section }}</div>
                            </div>
                            @foreach($fields as [$name, $label, $type, $col])
                                <div class="{{ $col }}">
                                    <label class="form-label">{{ $label }}</label>
                                    @if($type === 'textarea')
                                        <textarea class="form-control @error($name)is-invalid @enderror" name="{{ $name }}" rows="6">{{ old($name, $setting->{$name}) }}</textarea>
                                        <small class="text-muted">Leave a blank line between paragraphs.</small>
                                    @elseif($type === 'file')
                                        <input class="form-control @error($name)is-invalid @enderror" type="file" name="{{ $name }}" accept="image/*">
                                        @if($previews[$name] ?? null)
                                            <a href="{{ $previews[$name] }}" target="_blank" class="d-inline-block mt-2"><img src="{{ $previews[$name] }}" class="admin-gallery-preview-thumb" alt="{{ $label }}"></a>
                                        @endif
                                    @else
                                        <input class="form-control @error($name)is-invalid @enderror" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $setting->{$name}) }}">
                                    @endif
                                    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endforeach
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn btn-primary" type="submit"><i class="iconoir-check-circle me-1"></i>Save About Page</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
