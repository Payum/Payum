<?php
namespace Payum\Core\Tests\Bridge\Guzzle;

use Payum\Core\Bridge\Guzzle\HttpClient;
use Payum\Core\Bridge\Guzzle\HttpClientFactory;
use Payum\Core\HttpClientInterface;
use PHPUnit\Framework\TestCase;

class HttpClientFactoryTest extends TestCase
{
    public function testShouldReturnHttpClient()
    {
        $client = HttpClientFactory::create();

        $this->assertInstanceOf(HttpClientInterface::class, $client);
        $this->assertInstanceOf(HttpClient::class, $client);
    }
}
