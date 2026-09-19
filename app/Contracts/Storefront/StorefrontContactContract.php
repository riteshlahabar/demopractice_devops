<?php

namespace App\Contracts\Storefront;

use App\Models\Storefront\ContactMessage;
use Illuminate\Http\Request;

/**
 * SRP: storing a message sent from the website Contact Us form.
 */
interface StorefrontContactContract
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function store(Request $request, array $data): ContactMessage;
}
