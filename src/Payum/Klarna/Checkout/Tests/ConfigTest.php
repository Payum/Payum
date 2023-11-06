<?php
namespace Payum\Klarna\Checkout\Tests;

use Payum\Klarna\Checkout\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testShouldAllowSetOptions()
    {
        $config = new Config();
        $config->merchantId = 'aMerhId';
        $config->secret = 'aSecret';
        $config->contentType = 'aType';
        $config->baseUri = 'aMode';

        $this->assertSame('aMerhId', $config->merchantId);
        $this->assertSame('aSecret', $config->secret);
        $this->assertSame('aType', $config->contentType);
        $this->assertSame('aMode', $config->baseUri);
    }
}
