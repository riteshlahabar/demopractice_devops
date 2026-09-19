<?php

namespace App\Services\Admin\Reports;

use App\Contracts\Admin\Reports\ReportFilterOptionsContract;
use App\Data\Admin\Reports\ReportFilters;
use App\Models\DealerProfile;
use App\Models\Inventory\Warehouse;
use App\Models\User;

final class ReportFilterOptions implements ReportFilterOptionsContract
{
    public function for(array $filters): array
    {
        $options = [];

        if (in_array('channel', $filters, true)) {
            $options['channel'] = ReportFilters::CHANNELS;
        }

        if (in_array('period', $filters, true)) {
            $options['period'] = ReportFilters::PERIODS;
        }

        if (in_array('expiry_days', $filters, true)) {
            $options['expiry_days'] = ReportFilters::EXPIRY_WINDOWS;
        }

        if (in_array('salesman', $filters, true)) {
            $options['salesman_id'] = User::query()->where('role', User::ROLE_SALESMAN)->orderBy('name')->pluck('name', 'id')->all();
        }

        if (in_array('dealer', $filters, true)) {
            $options['dealer_id'] = DealerProfile::query()->with('user:id,name')->orderBy('firm_name')->get()
                ->mapWithKeys(fn (DealerProfile $profile): array => [$profile->user_id => $profile->firm_name ?: (string) $profile->user?->name])
                ->all();
        }

        if (in_array('warehouse', $filters, true)) {
            $options['warehouse_id'] = Warehouse::query()->orderBy('name')->pluck('name', 'id')->all();
        }

        return $options;
    }
}
