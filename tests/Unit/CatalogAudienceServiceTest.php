<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\Catalog\Api\CatalogAudienceService;
use PHPUnit\Framework\TestCase;

class CatalogAudienceServiceTest extends TestCase
{
    public function test_audience_rules_match_existing_catalog_access_behavior(): void
    {
        $service = new CatalogAudienceService;

        $guestHomepage = $service->forHomepage(null, 'dealer');
        $this->assertFalse($guestHomepage->allowed);
        $this->assertSame(401, $guestHomepage->status);
        $this->assertSame('Dealer homepage requires approved dealer login.', $guestHomepage->message);

        $customer = new User(['role' => User::ROLE_CUSTOMER]);
        $customerHomepage = $service->forHomepage($customer, 'dealer');
        $this->assertFalse($customerHomepage->allowed);
        $this->assertSame(403, $customerHomepage->status);

        $admin = new User(['role' => User::ROLE_ADMIN]);
        $this->assertTrue($service->forProducts($admin, 'dealer')->allowed);
        $this->assertSame('customer', $service->forProducts($admin, 'automatic')->audience);

        $dealer = new User(['role' => User::ROLE_DEALER]);
        $this->assertSame('dealer', $service->forProducts($dealer, 'automatic')->audience);
        $this->assertSame('customer', $service->forHomepage($dealer, 'automatic')->audience);
    }
}
