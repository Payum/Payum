<?php
namespace Payum\Klarna\Checkout\Tests;

use Payum\Klarna\Checkout\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new Config();
    }

    /**
     * @test
     */
    public function shouldAllowSetOptions()
    {
        $config = new Config();
        $config->merchantId = 'aMerhId';
        $config->secret = 'aSecret';
        $config->contentType = 'aType';
        $config->baseUri = 'aMode';
    }
}
