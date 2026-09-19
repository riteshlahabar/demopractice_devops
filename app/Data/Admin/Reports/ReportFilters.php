<?php

namespace App\Data\Admin\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * The filter values a report runs with, read once from the query string and
 * normalised, so no report parses request input itself.
 */
final readonly class ReportFilters
{
    public const SUPPORTED = ['date', 'channel', 'period', 'salesman', 'dealer', 'warehouse', 'expiry_days'];

    public const CHANNELS = ['dealer' => 'Dealer', 'customer' => 'Customer'];

    public const PERIODS = ['day' => 'Day wise', 'month' => 'Month wise'];

    public const EXPIRY_WINDOWS = ['30' => 'Next 30 days', '60' => 'Next 60 days', '90' => 'Next 90 days', 'all' => 'All batches'];

    public function __construct(
        public Carbon $from,
        public Carbon $to,
        public ?string $channel = null,
        public string $period = 'day',
        public ?int $salesmanId = null,
        public ?int $dealerId = null,
        public ?int $warehouseId = null,
        public ?int $expiryDays = 30,
    ) {}

    /**
     * Defaults to the current month; an unreadable date falls back to that
     * and a reversed range is swapped, rather than failing the page.
     */
    public static function fromRequest(Request $request): self
    {
        $from = self::date($request->query('from')) ?? now()->startOfMonth();
        $to = self::date($request->query('to')) ?? now();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        $channel = (string) $request->query('channel', '');
        $period = (string) $request->query('period', '');
        $expiry = (string) $request->query('expiry_days', '30');

        return new self(
            from: $from->copy()->startOfDay(),
            to: $to->copy()->endOfDay(),
            channel: array_key_exists($channel, self::CHANNELS) ? $channel : null,
            period: array_key_exists($period, self::PERIODS) ? $period : 'day',
            salesmanId: self::id($request->query('salesman_id')),
            dealerId: self::id($request->query('dealer_id')),
            warehouseId: self::id($request->query('warehouse_id')),
            expiryDays: match (true) {
                $expiry === 'all' => null,
                array_key_exists($expiry, self::EXPIRY_WINDOWS) => (int) $expiry,
                default => 30,
            },
        );
    }

    /**
     * Current values, keyed by the query-string name, for the filter form
     * and the export links.
     *
     * @return array<string, string>
     */
    public function toQuery(): array
    {
        return array_filter([
            'from' => $this->from->format('Y-m-d'),
            'to' => $this->to->format('Y-m-d'),
            'channel' => $this->channel,
            'period' => $this->period,
            'salesman_id' => $this->salesmanId ? (string) $this->salesmanId : null,
            'dealer_id' => $this->dealerId ? (string) $this->dealerId : null,
            'warehouse_id' => $this->warehouseId ? (string) $this->warehouseId : null,
            'expiry_days' => $this->expiryDays === null ? 'all' : (string) $this->expiryDays,
        ], fn (?string $value): bool => $value !== null && $value !== '');
    }

    private static function date(mixed $value): ?Carbon
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value);
        } catch (Throwable) {
            return null;
        }
    }

    private static function id(mixed $value): ?int
    {
        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }
}
