<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional;

class PaymentFactoryTest extends WebTestCase
{
    /**
     * @test
     */
    public function couldBeGetFromContainerAsService()
    {
        $factory = $this->container->get('payum.payment_factory');

        $this->assertInstanceOf('Payum\Bundle\PayumBundle\PaymentFactory', $factory);
    }
}