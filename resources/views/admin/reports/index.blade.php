@extends('admin.layouts.app')
@section('title', 'Reports Overview')
@section('content')
@php
    $tiles = [
        ['label' => 'B2B Sales', 'value' => $summary['b2b_sales'], 'icon' => 'shopping-bag', 'tone' => 'primary'],
        ['label' => 'B2C Sales', 'value' => $summary['b2c_sales'], 'icon' => 'shopping-cart', 'tone' => 'info'],
        ['label' => 'Collections', 'value' => $summary['collections'], 'icon' => 'credit-card', 'tone' => 'purple'],
        ['label' => 'Approved Expenses', 'value' => $summary['expenses'], 'icon' => 'file-text', 'tone' => 'warning'],
        ['label' => 'Payroll', 'value' => $summary['salary'], 'icon' => 'dollar-sign', 'tone' => 'danger'],
    ];
    $sectionIcons = ['ERP Reports' => 'trending-up', 'HRMS Reports' => 'users'];
    $linkTones = ['primary', 'info', 'purple', 'warning', 'danger'];
@endphp

<div class="report-page">
    <div class="card report-hero">
        <div class="card-body">
            <div class="d-flex align-items-center gap-3">
                <span class="report-hero-icon"><i data-feather="pie-chart"></i></span>
                <div>
                    <span class="report-hero-section">Reports</span>
                    <h4 class="report-hero-title">Reports Overview</h4>
                    <p class="report-hero-text">All-time totals from live records. Open any report below for date filters, details and Excel / PDF download.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3 row-cols-1 row-cols-sm-2 row-cols-xl-5">
        @foreach($tiles as $tile)
            <div class="col">
                <div class="main-tiles border-0 card-hover card o-hidden h-100 mb-0 report-tile report-tone-{{ $tile['tone'] }}">
                    <div class="card-body">
                        <div class="media static-top-widget">
                            <div class="media-body p-0">
                                <span class="report-tile-label">{{ $tile['label'] }}</span>
                                <h4 class="report-tile-value">Rs. {{ number_format((float) $tile['value'], 2) }}</h4>
                            </div>
                            <div class="align-self-center text-center report-tile-icon"><i data-feather="{{ $tile['icon'] }}"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @foreach($sections as $sectionTitle => $sectionReports)
        <div class="card report-table-card mb-3">
            <div class="card-header border-0">
                <h4 class="report-section-title"><i data-feather="{{ $sectionIcons[$sectionTitle] ?? 'bar-chart-2' }}"></i>{{ $sectionTitle }}</h4>
            </div>
            <div class="card-body pt-0">
                <div class="row g-3">
                    @foreach(array_values($sectionReports) as $index => $item)
                        <div class="col-md-6 col-xl-4">
                            <a href="{{ route('admin.report.show', $item->key()) }}" class="report-overview-link report-tone-{{ $linkTones[$index % count($linkTones)] }}">
                                <span class="report-tile-icon"><i data-feather="{{ $item->icon() }}"></i></span>
                                <span>
                                    <h6>{{ $item->title() }}</h6>
                                    <small>{{ $item->description() }}</small>
                                </span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
