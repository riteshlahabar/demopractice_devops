<?php

namespace App\Services\Admin\Reports;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * One place that decides how a report value reads, shared by the page and
 * the Excel/PDF export so both show the same text.
 */
final class ReportValueFormatter
{
    public function format(mixed $value, string $type = 'text'): string
    {
        return match ($type) {
            'money' => 'Rs. '.number_format((float) $value, 2),
            'number' => fmod((float) $value, 1.0) === 0.0 ? number_format((float) $value) : number_format((float) $value, 2),
            'percent' => number_format((float) $value, 1).'%',
            'date' => $value ? Carbon::parse($value)->format('d-m-Y') : '-',
            'status' => $value === null || $value === '' ? '-' : Str::of((string) $value)->replace('_', ' ')->title()->toString(),
            default => $value === null || $value === '' ? '-' : (string) $value,
        };
    }
}
