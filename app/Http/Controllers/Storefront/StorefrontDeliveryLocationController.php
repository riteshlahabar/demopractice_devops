<?php

namespace App\Http\Controllers\Storefront;

use App\Contracts\Storefront\StorefrontDeliveryLocationContract;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class StorefrontDeliveryLocationController extends Controller
{
    public function __construct(
        private readonly StorefrontDeliveryLocationContract $location
    ) {}

    public function update(Request $request): RedirectResponse
    {
        $districtCode = (int) $request->validate([
            'district_code' => ['required', 'integer', 'min:1'],
        ])['district_code'];

        $this->location->select($request, $districtCode);

        return back();
    }
}
