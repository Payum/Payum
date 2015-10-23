<?php
namespace Payum\Core\Tests\Bridge\Guzzle;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Payum\Core\Bridge\Guzzle\HttpClient;
use Payum\Core\Bridge\Guzzle\HttpClientFactory;
use Payum\Core\HttpClientInterface;

class HttpClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldReturnHttpClient()
    {
        $client = HttpClientFactory::create();

        $this->assertInstanceOf(HttpClientInterface::class, $client);
        $this->assertInstanceOf(HttpClient::class, $client);
    }

    /**
     * @test
     */
    public function shouldReturnGuzzleClient()
    {
        $client = HttpClientFactory::createGuzzle();

        $this->assertInstanceOf(GuzzleClientInterface::class, $client);
    }
}
