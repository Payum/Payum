<?php

namespace Payum\Stripe\Tests;

use Payum\Stripe\Keys;
use PHPUnit\Framework\TestCase;

class KeysTest extends TestCase
{
    public function testSouldAllowGetPublishableKeySetInConstructor(): void
    {
        $keys = new Keys('thePublishableKey', 'aSecretKey');

        $this->assertSame('thePublishableKey', $keys->getPublishableKey());
    }

    public function testShouldAllowGetSecretKeySetInConstructor(): void
    {
        $keys = new Keys('aPublishableKey', 'theSecretKey');

        $this->assertSame('theSecretKey', $keys->getSecretKey());
    }
}
