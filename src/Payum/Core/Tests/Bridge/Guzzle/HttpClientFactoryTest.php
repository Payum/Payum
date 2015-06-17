<?php
namespace Payum\Core\Tests\Bridge\Guzzle;

use Payum\Core\Bridge\Guzzle\HttpClientFactory;

class ClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldReturnHttpClient()
    {
        $client = HttpClientFactory::create();

        $this->assertInstanceOf('Payum\Core\HttpClientInterface', $client);
        $this->assertInstanceOf('Payum\Core\Bridge\Guzzle\HttpClient', $client);
    }
}
