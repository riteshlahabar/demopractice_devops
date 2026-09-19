<?php

namespace App\Contracts\Support;

interface TransactionManagerContract
{
    public function run(callable $callback): mixed;
}
