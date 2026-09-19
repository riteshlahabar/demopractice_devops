@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
@php
    $can = ($moduleAccess ?? []) + ['view' => true, 'create' => true, 'edit' => true, 'delete' => true];
    $query = request()->only(['type', 'placement', 'section_key', 'row_title']);
    $paragraphs = $record->answerParagraphs();
    $categoryLabel = $record->category_label;
    $isActive = (bool) $record->is_active;
    $websiteUrl = route('store.page', array_filter(['page' => 'faq', 'faq_category' => $record->category]));
@endphp

<div class="report-page faq-show-page">
    <div class="card report-hero">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-start gap-3">
                    <span class="faq-show-mark"><i data-feather="help-circle"></i></span>
                    <div>
                        <span class="report-hero-section">{{ $module['singular'] }}</span>
                        <h4 class="report-hero-title">{{ $record->question }}</h4>
                        <div class="people-hero-meta">
                            <span class="badge {{ $isActive ? 'badge-light-success' : 'badge-light-danger' }}">{{ $isActive ? 'Active' : 'Inactive' }}</span>
                            @if($categoryLabel !== '')<span><i data-feather="grid"></i>{{ $categoryLabel }}</span>@endif
                            <span><i data-feather="bar-chart-2"></i>Position {{ (int) $record->sort_order }}</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn report-reset" href="{{ route($module['route'].'.index', $query) }}"><i data-feather="arrow-left"></i>Back</a>
                    <a class="btn report-reset" href="{{ $websiteUrl }}" target="_blank" rel="noopener"><i data-feather="external-link"></i>View on Website</a>
                    @if(($module['can_edit'] ?? true) && $can['edit'])
                        <a class="btn btn-theme report-apply" href="{{ route($module['route'].'.edit', array_merge([$record->getKey()], $query)) }}"><i data-feather="edit-2"></i>Edit</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card report-table-card mb-0">
        <div class="card-header border-0">
            <div class="card-header-title d-flex align-items-center gap-2">
                <span class="people-info-icon"><i data-feather="message-square"></i></span>
                <h4>Answer</h4>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="faq-show-preview">
                <div class="faq-show-question">
                    <span>{{ $record->question }}</span>
                    <i data-feather="chevron-down"></i>
                </div>
                <div class="faq-show-answer">
                    @forelse($paragraphs as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @empty
                        <p class="text-muted mb-0">No answer text saved yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
