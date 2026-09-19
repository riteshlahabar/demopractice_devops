<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserDisplayEmailTest extends TestCase
{
    public function test_placeholder_and_blank_emails_are_hidden(): void
    {
        $this->assertNull(User::displayEmail('9876543210@dealer.bawaskar.local'));
        $this->assertNull(User::displayEmail('9876543210@Customer.Bawaskar.Local'));
        $this->assertNull(User::displayEmail(null));
        $this->assertNull(User::displayEmail('  '));
        $this->assertSame('ramesh@example.com', User::displayEmail(' ramesh@example.com '));
    }
}
