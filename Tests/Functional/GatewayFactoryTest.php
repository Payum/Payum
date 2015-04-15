<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional;

class GatewayFactoryTest extends WebTestCase
{
    /**
     * @test
     */
    public function couldBeGetFromContainerAsService()
    {
        $factory = $this->container->get('payum.gateway_factory');

        $this->assertInstanceOf('Payum\Bundle\PayumBundle\GatewayFactory', $factory);
    }
}