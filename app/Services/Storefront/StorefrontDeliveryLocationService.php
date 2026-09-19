<?php

namespace App\Services\Storefront;

use App\Contracts\Location\LocationDirectoryContract;
use App\Contracts\Storefront\Repositories\DeliveryAreaRepositoryContract;
use App\Contracts\Storefront\StorefrontDeliveryLocationContract;
use App\Services\Storefront\Session\StorefrontSessionKeys;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;

/**
 * Admin Delivery Areas decide the list. Until any are added, every district
 * of the default state (config storefront.default_delivery_state_code) is
 * offered, so the modal is never empty.
 */
final class StorefrontDeliveryLocationService implements StorefrontDeliveryLocationContract
{
    public function __construct(
        private readonly DeliveryAreaRepositoryContract $areas,
        private readonly LocationDirectoryContract $directory,
        private readonly Config $config
    ) {}

    public function context(Request $request): array
    {
        $areas = $this->areas();
        $selectedCode = (int) $request->session()->get(StorefrontSessionKeys::DELIVERY_DISTRICT, 0);

        return [
            'areas' => $areas,
            'selected' => $this->find($areas, $selectedCode),
        ];
    }

    public function select(Request $request, int $districtCode): bool
    {
        if ($this->find($this->areas(), $districtCode) === null) {
            return false;
        }

        $request->session()->put(StorefrontSessionKeys::DELIVERY_DISTRICT, $districtCode);

        return true;
    }

    /**
     * @return array<int, array{code: int, name: string, state: string, min_order: float|null}>
     */
    private function areas(): array
    {
        $areas = $this->areas->active();

        if ($areas !== []) {
            return $areas;
        }

        $stateCode = (int) $this->config->get('storefront.default_delivery_state_code', 27);
        $stateName = $this->directory->states()[$stateCode] ?? '';
        $fallback = [];

        foreach ($this->directory->districts($stateCode) as $code => $name) {
            $fallback[] = ['code' => (int) $code, 'name' => $name, 'state' => $stateName, 'min_order' => null];
        }

        return $fallback;
    }

    /**
     * @param  array<int, array{code: int, name: string, state: string, min_order: float|null}>  $areas
     * @return array{code: int, name: string, state: string, min_order: float|null}|null
     */
    private function find(array $areas, int $code): ?array
    {
        if ($code < 1) {
            return null;
        }

        foreach ($areas as $area) {
            if ($area['code'] === $code) {
                return $area;
            }
        }

        return null;
    }
}
