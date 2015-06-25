<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional;

use Payum\Core\HttpClientInterface;

class HttpClientTest extends WebTestCase
{
    /**
     * @test
     */
    public function couldBeGetFromContainerAsService()
    {
        $client = $this->container->get('payum.http_client');

        $this->assertInstanceOf(HttpClientInterface::class, $client);
    }
}