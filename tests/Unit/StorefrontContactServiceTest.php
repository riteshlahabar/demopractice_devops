<?php

namespace Tests\Unit;

use App\Http\Requests\Storefront\ContactMessageRequest;
use App\Models\CompanySetting;
use App\Models\Storefront\ContactMessage;
use Tests\TestCase;

class StorefrontContactServiceTest extends TestCase
{
    public function test_the_map_uses_the_saved_link_first_then_the_address(): void
    {
        $withLink = new CompanySetting(['address' => 'Shrirampur, Maharashtra', 'map_embed_url' => 'https://www.google.com/maps/embed?pb=abc']);
        $this->assertSame('https://www.google.com/maps/embed?pb=abc', $withLink->mapEmbedUrl());

        $fromAddress = new CompanySetting(['address' => "Main Road,  Shrirampur\nMaharashtra 413709"]);
        $this->assertSame(
            'https://www.google.com/maps?q='.rawurlencode('Main Road, Shrirampur Maharashtra 413709').'&output=embed',
            $fromAddress->mapEmbedUrl()
        );

        $this->assertNull((new CompanySetting(['address' => '   ']))->mapEmbedUrl());
    }

    public function test_the_honeypot_field_must_stay_empty(): void
    {
        $rules = (new ContactMessageRequest)->rules();

        $this->assertSame(['prohibited'], $rules['website']);
        $this->assertContains('required', $rules['message']);
        $this->assertContains('email', $rules['email']);
    }

    public function test_first_and_last_name_become_one_name(): void
    {
        $this->assertSame('Ritesh Patil', ContactMessage::fullName(' Ritesh ', ' Patil '));
        $this->assertSame('Ritesh', ContactMessage::fullName('Ritesh', null));
        $this->assertSame('', ContactMessage::fullName('  ', ''));
    }
}
