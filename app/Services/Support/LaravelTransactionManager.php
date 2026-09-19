<?php

namespace App\Services\Support;

use App\Contracts\Support\TransactionManagerContract;
use Illuminate\Support\Facades\DB;

final class LaravelTransactionManager implements TransactionManagerContract
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
