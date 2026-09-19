@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
@php
    $can = ($moduleAccess ?? []) + ['view' => true, 'create' => true, 'edit' => true, 'delete' => true];
    $summary ??= \App\Data\Admin\People\PersonSummary::empty();
    $query = request()->only(['type', 'placement', 'section_key', 'row_title']);
    $email = \App\Models\User::displayEmail($record->email);
    $initials = collect(preg_split('/\s+/', trim((string) $record->name)) ?: [])->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    $statusBadge = fn (?string $status): string => match (strtolower((string) $status)) {
        'active', 'approved', 'delivered', 'paid' => 'badge-light-success',
        'inactive', 'cancelled', 'rejected' => 'badge-light-danger',
        default => 'badge-light-warning',
    };
    $dash = fn ($value) => filled($value) ? $value : '-';
    $contact = [
        'Email' => $email ? '<a href="mailto:'.e($email).'">'.e($email).'</a>' : '-',
        'Mobile' => $record->mobile ? '<a href="tel:'.e($record->mobile).'">'.e($record->mobile).'</a>' : '-',
        'Mobile Verified' => $record->mobile_verified_at ? e($record->mobile_verified_at->format('d-m-Y')) : 'No',
        'Registered On' => $record->created_at ? e($record->created_at->format('d-m-Y')) : '-',
        'Last Login' => $record->last_login_at ? e($record->last_login_at->format('d-m-Y h:i A')) : '-',
    ];
    $location = [
        'State' => $dash($record->state_name),
        'District' => $dash($record->district_name),
        'Taluka' => $dash($record->subdistrict_name),
        'City / Village' => $dash($record->city_village),
        'Pincode' => $dash($record->pincode),
    ];
@endphp

<div class="report-page people-page">
    <div class="card report-hero">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    @if($record->profile_photo_url)
                        <img src="{{ $record->profile_photo_url }}" alt="{{ $record->name }}" class="people-avatar">
                    @else
                        <span class="people-avatar people-avatar-initials">{{ $initials ?: '?' }}</span>
                    @endif
                    <div>
                        <span class="report-hero-section">{{ $module['singular'] }}</span>
                        <h4 class="report-hero-title">{{ $record->name ?: '-' }}</h4>
                        <div class="people-hero-meta">
                            <span class="badge {{ $statusBadge($record->status) }}">{{ str($record->status)->replace('_', ' ')->title() }}</span>
                            @if($summary->code !== '')<span><i data-feather="hash"></i>{{ $summary->code }}</span>@endif
                            @if($record->role === \App\Models\User::ROLE_DEALER && $record->dealerProfile?->firm_name)<span><i data-feather="briefcase"></i>{{ $record->dealerProfile->firm_name }}</span>@endif
                            @if($record->district_name)<span><i data-feather="map-pin"></i>{{ $record->district_name }}</span>@endif
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn report-reset" href="{{ route($module['route'].'.index', $query) }}"><i data-feather="arrow-left"></i>Back</a>
                    @if(($module['can_edit'] ?? true) && $can['edit'])
                        <a class="btn btn-theme report-apply" href="{{ route($module['route'].'.edit', array_merge([$record->getKey()], $query)) }}"><i data-feather="edit-2"></i>Edit</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($summary->tiles !== [])
        <div class="row g-3 mb-3 row-cols-1 row-cols-sm-2 row-cols-xl-{{ min(4, count($summary->tiles)) }}">
            @foreach($summary->tiles as $tile)
                <div class="col">
                    <div class="main-tiles border-0 card-hover card o-hidden h-100 mb-0 report-tile report-tone-{{ $tile['tone'] }}">
                        <div class="card-body">
                            <div class="media static-top-widget">
                                <div class="media-body p-0">
                                    <span class="report-tile-label">{{ $tile['label'] }}</span>
                                    <h4 class="report-tile-value">{{ $tile['value'] }}</h4>
                                </div>
                                <div class="align-self-center text-center report-tile-icon">
                                    <i data-feather="{{ $tile['icon'] }}"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="row g-3 mb-3">
        @foreach([
            ['title' => 'Contact Information', 'icon' => 'phone', 'rows' => $contact, 'html' => true],
            ['title' => $summary->detailsTitle, 'icon' => 'file-text', 'rows' => $summary->details, 'html' => false],
            ['title' => 'Location', 'icon' => 'map', 'rows' => $location, 'html' => false],
        ] as $panel)
            @continue($panel['rows'] === [])
            <div class="col-xl-4 col-md-6">
                <div class="card report-table-card people-info-card h-100 mb-0">
                    <div class="card-header border-0">
                        <div class="card-header-title d-flex align-items-center gap-2">
                            <span class="people-info-icon"><i data-feather="{{ $panel['icon'] }}"></i></span>
                            <h4>{{ $panel['title'] }}</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="people-info-list">
                            @foreach($panel['rows'] as $label => $value)
                                <li>
                                    <span class="people-info-label">{{ $label }}</span>
                                    <span class="people-info-value">@if($panel['html']){!! $value !!}@else{{ $value }}@endif</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($summary->tableTitle !== '')
        <div class="card report-table-card">
            <div class="card-header border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="card-header-title"><h4>{{ $summary->tableTitle }}</h4></div>
                <span class="report-meta">Latest {{ count($summary->tableRows) }}</span>
            </div>
            <div class="card-body pt-0">
                @if($summary->tableRows === [])
                    <div class="report-empty">
                        <span class="report-empty-icon"><i data-feather="inbox"></i></span>
                        <h5>No records found</h5>
                        <p>{{ $summary->tableEmpty }}</p>
                    </div>
                @else
                    <div class="table-responsive report-table-wrap">
                        <table class="table align-middle mb-0 report-table">
                            <thead>
                                <tr>
                                    @foreach($summary->tableColumns as $column)
                                        <th @class(['text-end' => ($column['align'] ?? '') === 'end'])>{{ $column['label'] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($summary->tableRows as $row)
                                    <tr>
                                        @foreach($summary->tableColumns as $column)
                                            @php($text = $row[$column['key']] ?? '-')
                                            @if($loop->first)
                                                <td class="fw-semibold text-title">@if(! empty($row['url']))<a href="{{ $row['url'] }}" class="theme-color">{{ $text }}</a>@else{{ $text }}@endif</td>
                                            @elseif(! empty($column['status']))
                                                <td>@if($text !== '-')<span class="badge {{ $statusBadge($text) }}">{{ $text }}</span>@else - @endif</td>
                                            @else
                                                <td @class(['text-end text-nowrap' => ($column['align'] ?? '') === 'end'])>{{ $text }}</td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
