<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional;

use Payum\Bundle\PayumBundle\GatewayFactory;

class GatewayFactoryTest extends WebTestCase
{
    /**
     * @test
     */
    public function couldBeGetFromContainerAsService()
    {
        $factory = $this->container->get('payum.gateway_factory');

        $this->assertInstanceOf(GatewayFactory::class, $factory);
    }
}