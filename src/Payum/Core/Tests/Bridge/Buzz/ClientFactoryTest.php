<?php
namespace Payum\Core\Tests\Bridge\Buzz;

use Payum\Core\Bridge\Buzz\ClientFactory;

class ClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldReturnCurlClient()
    {
        $clinet = ClientFactory::createCurl();

        $this->assertInstanceOf('Buzz\Client\Curl', $clinet);
    }
}