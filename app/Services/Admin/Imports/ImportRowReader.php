<?php

namespace App\Services\Admin\Imports;

use App\Contracts\Admin\Imports\ImportRowReaderContract;
use Illuminate\Support\Str;

final class ImportRowReader implements ImportRowReaderContract
{
    public function header(string $header): string
    {
        $header = Str::of($header)->lower()->trim()->toString();
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);

        return trim((string) $header, '_');
    }

    public function firstFilled(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            $key = $this->header($key);

            if (isset($row[$key]) && trim((string) $row[$key]) !== '') {
                return trim((string) $row[$key]);
            }
        }

        return null;
    }
}
