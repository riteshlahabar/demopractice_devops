@extends('admin.layouts.app')
@section('title', $report->title())
@section('content')
@php
    $query = $filters->toQuery();
    $selectFilters = [
        'channel' => ['name' => 'channel', 'label' => 'Channel', 'all' => 'All channels'],
        'period' => ['name' => 'period', 'label' => 'Group By', 'all' => null],
        'salesman' => ['name' => 'salesman_id', 'label' => 'Salesman', 'all' => 'All salesmen'],
        'dealer' => ['name' => 'dealer_id', 'label' => 'Dealer', 'all' => 'All dealers'],
        'warehouse' => ['name' => 'warehouse_id', 'label' => 'Warehouse', 'all' => 'All warehouses'],
        'expiry_days' => ['name' => 'expiry_days', 'label' => 'Expiry', 'all' => null],
    ];
    $numericTypes = ['money', 'number', 'percent'];
    $summableTypes = ['money', 'number'];
    $isHrms = $report->section() === \App\Contracts\Admin\Reports\ReportContract::SECTION_HRMS;
    $badgeFor = function (?string $value): string {
        $value = strtolower((string) $value);
        return match (true) {
            in_array($value, ['approved', 'paid', 'delivered', 'completed', 'verified', 'collected', 'ok', 'closed', 'disbursed', 'refunded'], true) => 'badge-light-success',
            in_array($value, ['pending', 'requested', 'draft', 'planned', 'expiring soon', 'low stock', 'received', 'salesman review', 'admin review', 'packing'], true) => 'badge-light-warning',
            in_array($value, ['rejected', 'cancelled', 'failed', 'expired'], true) => 'badge-light-danger',
            default => 'badge-light-primary',
        };
    };
    $totals = [];
    if (count($result->rows) > 1) {
        foreach ($result->columns as $column) {
            if (in_array($column['type'] ?? 'text', $summableTypes, true)) {
                $totals[$column['key']] = array_sum(array_map(fn (array $row) => (float) ($row[$column['key']] ?? 0), $result->rows));
            }
        }
    }
    $cardCount = max(1, min(5, count($result->cards)));
@endphp

<div class="report-page">
    <div class="card report-hero">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="report-hero-icon"><i data-feather="{{ $report->icon() }}"></i></span>
                    <div>
                        <span class="report-hero-section">{{ $isHrms ? 'HRMS Report' : 'ERP Report' }}</span>
                        <h4 class="report-hero-title">{{ $report->title() }}</h4>
                        @if($report->description())<p class="report-hero-text">{{ $report->description() }}</p>@endif
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn report-export report-export-excel" href="{{ route('admin.report.export', array_merge(['report' => $report->key(), 'format' => 'excel'], $query)) }}"><i data-feather="download"></i>Excel</a>
                    <a class="btn report-export report-export-pdf" href="{{ route('admin.report.export', array_merge(['report' => $report->key(), 'format' => 'pdf'], $query)) }}"><i data-feather="file-text"></i>PDF</a>
                </div>
            </div>

            <form class="report-filter-bar" method="GET" action="{{ route('admin.report.show', $report->key()) }}">
                @if(in_array('date', $report->filters(), true))
                    <div class="report-filter-field">
                        <label class="form-label">From</label>
                        <input type="date" name="from" value="{{ $query['from'] }}" class="form-control">
                    </div>
                    <div class="report-filter-field">
                        <label class="form-label">To</label>
                        <input type="date" name="to" value="{{ $query['to'] }}" class="form-control">
                    </div>
                @endif

                @foreach($report->filters() as $filterKey)
                    @continue(! isset($selectFilters[$filterKey]))
                    @php($select = $selectFilters[$filterKey])
                    <div class="report-filter-field">
                        <label class="form-label">{{ $select['label'] }}</label>
                        <select class="form-select" name="{{ $select['name'] }}">
                            @if($select['all'])<option value="">{{ $select['all'] }}</option>@endif
                            @foreach($filterOptions[$select['name']] ?? [] as $optionValue => $optionLabel)
                                <option value="{{ $optionValue }}" @selected((string) ($query[$select['name']] ?? '') === (string) $optionValue)>{{ $optionLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach

                <div class="report-filter-actions">
                    <button class="btn btn-theme report-apply" type="submit"><i data-feather="filter"></i>Apply</button>
                    <a class="btn report-reset" href="{{ route('admin.report.show', $report->key()) }}"><i data-feather="rotate-ccw"></i>Reset</a>
                </div>
            </form>
        </div>
    </div>

    @if($result->cards !== [])
        <div class="row g-3 mb-3 row-cols-1 row-cols-sm-2 row-cols-xl-{{ $cardCount }}">
            @foreach($result->cards as $card)
                <div class="col">
                    <div class="main-tiles border-0 card-hover card o-hidden h-100 mb-0 report-tile report-tone-{{ $card['tone'] ?? 'primary' }}">
                        <div class="card-body">
                            <div class="media static-top-widget">
                                <div class="media-body p-0">
                                    <span class="report-tile-label">{{ $card['label'] }}</span>
                                    <h4 class="report-tile-value">{{ $formatter->format($card['value'], $card['type'] ?? 'text') }}</h4>
                                </div>
                                <div class="align-self-center text-center report-tile-icon">
                                    <i data-feather="{{ $card['icon'] ?? 'bar-chart-2' }}"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="card report-table-card">
        <div class="card-header border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="card-header-title"><h4>Report Details</h4></div>
            <span class="report-meta">
                {{ number_format(count($result->rows)) }} {{ \Illuminate\Support\Str::plural('row', count($result->rows)) }}
                @if(in_array('date', $report->filters(), true))
                    &middot; {{ $filters->from->format('d M Y') }} &ndash; {{ $filters->to->format('d M Y') }}
                @endif
            </span>
        </div>
        <div class="card-body pt-0">
            @if($result->note)
                <div class="report-note"><i data-feather="info"></i><span>{{ $result->note }}</span></div>
            @endif

            @if($result->rows === [])
                <div class="report-empty">
                    <span class="report-empty-icon"><i data-feather="inbox"></i></span>
                    <h5>No records found</h5>
                    <p>Try a wider date range or clear the filters.</p>
                </div>
            @else
                <div class="table-responsive report-table-wrap">
                    <table class="table align-middle mb-0 report-table">
                        <thead>
                            <tr>
                                @foreach($result->columns as $column)
                                    <th @class(['text-end' => in_array($column['type'] ?? 'text', $numericTypes, true)])>{{ $column['label'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($result->rows as $row)
                                <tr>
                                    @foreach($result->columns as $column)
                                        @php($type = $column['type'] ?? 'text')
                                        @php($text = $formatter->format($row[$column['key']] ?? null, $type))
                                        @if(in_array($type, $numericTypes, true))
                                            <td class="text-end text-nowrap">{{ $text }}</td>
                                        @elseif($type === 'status' || $column['key'] === 'state')
                                            <td>@if($text !== '-')<span class="badge {{ $badgeFor($text) }}">{{ $text }}</span>@else - @endif</td>
                                        @elseif($loop->first)
                                            <td class="fw-semibold text-title">{{ $text }}</td>
                                        @else
                                            <td>{{ $text }}</td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                        @if($totals !== [])
                            <tfoot>
                                <tr>
                                    @foreach($result->columns as $column)
                                        @if($loop->first)
                                            <td>Total</td>
                                        @elseif(array_key_exists($column['key'], $totals))
                                            <td class="text-end text-nowrap">{{ $formatter->format($totals[$column['key']], $column['type']) }}</td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endforeach
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
