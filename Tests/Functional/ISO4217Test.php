<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional;

class ISO4217Test extends WebTestCase
{
    /**
     * @test
     */
    public function couldBeGetFromContainerAsService()
    {
        $service = $this->container->get('payum.iso4217');

        $this->assertInstanceOf('Payum\ISO4217\ISO4217', $service);
    }
}