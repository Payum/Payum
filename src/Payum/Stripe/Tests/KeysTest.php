<?php

namespace Payum\Stripe\Tests;

use Payum\Stripe\Keys;

class KeysTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function souldAllowGetPublishableKeySetInConstructor()
    {
        $keys = new Keys('thePublishableKey', 'aSecretKey');

        $this->assertSame('thePublishableKey', $keys->getPublishableKey());
    }

    /**
     * @test
     */
    public function shouldAllowGetSecretKeySetInConstructor()
    {
        $keys = new Keys('aPublishableKey', 'theSecretKey');

        $this->assertSame('theSecretKey', $keys->getSecretKey());
    }
}
