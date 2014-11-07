<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional;

class BuzzClientTest extends WebTestCase
{
    /**
     * @test
     */
    public function couldBeGetFromContainerAsService()
    {
        $client = $this->container->get('payum.buzz.client');

        $this->assertInstanceOf('Buzz\Client\Curl', $client);
    }
}