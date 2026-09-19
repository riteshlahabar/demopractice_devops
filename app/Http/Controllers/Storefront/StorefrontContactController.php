<?php

namespace App\Http\Controllers\Storefront;

use App\Contracts\Storefront\StorefrontContactContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\ContactMessageRequest;
use Illuminate\Http\RedirectResponse;

final class StorefrontContactController extends Controller
{
    public function __construct(
        private readonly StorefrontContactContract $contact
    ) {}

    public function store(ContactMessageRequest $request): RedirectResponse
    {
        $this->contact->store($request, $request->validated());

        return redirect()
            ->route('store.page', ['page' => 'contact-us'])
            ->with('contact_success', web_t('contact.sent', 'Thank you. Your message has been sent, our team will contact you soon.'));
    }
}
