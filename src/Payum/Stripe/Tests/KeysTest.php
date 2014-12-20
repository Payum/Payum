<?php
namespace Payum\Stripe\Tests;

use Payum\Stripe\Keys;

class KeysTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithPublishableKeyAndSecretOne()
    {
        new Keys('aPublishableKey', 'aSecretKey');
    }

    /**
     * @test
     */
    public function souldAllowGetPublishableKeySetInConstructor()
    {
        $keys = new Keys('thePublishableKey', 'aSecretKey');

        $this->assertEquals('thePublishableKey', $keys->getPublishableKey());
    }

    /**
     * @test
     */
    public function shouldAllowGetSecretKeySetInConstructor()
    {
        $keys = new Keys('aPublishableKey', 'theSecretKey');

        $this->assertEquals('theSecretKey', $keys->getSecretKey());
    }
}
