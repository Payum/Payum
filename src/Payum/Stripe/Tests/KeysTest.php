<?php
namespace Payum\Stripe\Tests;

use Payum\Stripe\Keys;

class KeysTest extends \PHPUnit\Framework\TestCase
{
    public function testSouldAllowGetPublishableKeySetInConstructor()
    {
        $keys = new Keys('thePublishableKey', 'aSecretKey');

        $this->assertSame('thePublishableKey', $keys->getPublishableKey());
    }

    public function testShouldAllowGetSecretKeySetInConstructor()
    {
        $keys = new Keys('aPublishableKey', 'theSecretKey');

        $this->assertSame('theSecretKey', $keys->getSecretKey());
    }
}
