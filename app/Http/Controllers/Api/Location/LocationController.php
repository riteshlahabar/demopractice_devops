<?php

namespace App\Http\Controllers\Api\Location;

use App\Contracts\Location\LocationDirectoryContract;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public cascading dropdown data for registration forms (apps, website and
 * admin): states, then districts of a state, then talukas of a district.
 */
final class LocationController extends ApiController
{
    public function __construct(private readonly LocationDirectoryContract $directory) {}

    public function states(): JsonResponse
    {
        return $this->success(['states' => $this->items($this->directory->states())]);
    }

    public function districts(Request $request): JsonResponse
    {
        $state = (int) $request->validate(['state' => ['required', 'integer', 'min:1']])['state'];

        return $this->success(['districts' => $this->items($this->directory->districts($state))]);
    }

    public function subdistricts(Request $request): JsonResponse
    {
        $district = (int) $request->validate(['district' => ['required', 'integer', 'min:1']])['district'];

        return $this->success(['subdistricts' => $this->items($this->directory->subdistricts($district))]);
    }

    /**
     * @param  array<int|string, string>  $items
     * @return array<int, array{code: string, name: string}>
     */
    private function items(array $items): array
    {
        return array_map(
            fn (int|string $code, string $name): array => ['code' => (string) $code, 'name' => $name],
            array_keys($items),
            $items,
        );
    }
}
