<?php
namespace Payum\OmnipayBridge\Tests;

use Omnipay\Common\GatewayInterface;

abstract class DummyGateway implements GatewayInterface
{
    public function purchase() {}

    public function completePurchase() {}
}
