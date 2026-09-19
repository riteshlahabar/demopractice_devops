<?php

namespace Tests\Unit;

use App\Contracts\Location\LocationDirectoryContract;
use App\Contracts\Storefront\Repositories\DeliveryAreaRepositoryContract;
use App\Services\Location\DistrictSelectionService;
use App\Services\Storefront\StorefrontDeliveryLocationService;
use Illuminate\Config\Repository as Config;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StorefrontDeliveryLocationServiceTest extends TestCase
{
    public function test_admin_areas_are_listed_and_only_those_can_be_selected(): void
    {
        $service = $this->service([['code' => 490, 'name' => 'Pune', 'state' => 'Maharashtra', 'min_order' => 500.0]]);
        $request = $this->request();

        $this->assertFalse($service->select($request, 491));
        $this->assertNull($service->context($request)['selected']);

        $this->assertTrue($service->select($request, 490));
        $context = $service->context($request);
        $this->assertCount(1, $context['areas']);
        $this->assertSame('Pune', $context['selected']['name']);
        $this->assertSame(500.0, $context['selected']['min_order']);
    }

    public function test_without_admin_areas_the_default_state_districts_are_listed(): void
    {
        $context = $this->service([])->context($this->request());

        $this->assertSame(['Nashik', 'Pune'], array_column($context['areas'], 'name'));
        $this->assertSame('Maharashtra', $context['areas'][0]['state']);
        $this->assertNull($context['areas'][0]['min_order']);
    }

    public function test_district_selection_resolves_names_and_rejects_a_district_of_another_state(): void
    {
        $selection = new DistrictSelectionService($this->directory());

        $this->assertSame(
            ['state_code' => 27, 'state_name' => 'Maharashtra', 'district_code' => 490, 'district_name' => 'Pune'],
            $selection->attributes(['state_code' => '27', 'district_code' => '490'])
        );

        $this->expectException(ValidationException::class);
        $selection->attributes(['state_code' => '27', 'district_code' => '999']);
    }

    /**
     * @param  array<int, array{code: int, name: string, state: string, min_order: float|null}>  $areas
     */
    private function service(array $areas): StorefrontDeliveryLocationService
    {
        $repository = new class($areas) implements DeliveryAreaRepositoryContract
        {
            public function __construct(private readonly array $areas) {}

            public function active(): array
            {
                return $this->areas;
            }
        };

        return new StorefrontDeliveryLocationService($repository, $this->directory(), new Config(['storefront' => ['default_delivery_state_code' => 27]]));
    }

    private function directory(): LocationDirectoryContract
    {
        return new class implements LocationDirectoryContract
        {
            public function states(): array
            {
                return [27 => 'Maharashtra', 29 => 'Karnataka'];
            }

            public function districts(int $stateCode): array
            {
                return $stateCode === 27 ? [516 => 'Nashik', 490 => 'Pune'] : [];
            }

            public function subdistricts(int $districtCode): array
            {
                return [];
            }
        };
    }

    private function request(): Request
    {
        $request = Request::create('/');
        $request->setLaravelSession(new Store('test', new ArraySessionHandler(10)));

        return $request;
    }
}
