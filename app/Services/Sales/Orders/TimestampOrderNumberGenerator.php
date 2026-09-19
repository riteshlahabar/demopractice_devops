<?php

namespace App\Services\Sales\Orders;

use App\Contracts\Sales\Orders\OrderNumberGeneratorContract;

final class TimestampOrderNumberGenerator implements OrderNumberGeneratorContract
{
    public function next(string $orderType): string
    {
        $prefix = $orderType === 'dealer' ? 'DO' : 'CO';

        return $prefix
            .now()->format('ymdHis')
            .random_int(100, 999);
    }
}
