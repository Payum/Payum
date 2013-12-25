<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\Action;

use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;

class GetHttpQueryActionTest extends WebTestCase
{
    /**
     * @test
     */
    public function shouldAllowGetAsServiceFromContainer()
    {
        static::createClient();

        $service = static::$kernel->getContainer()->get('payum.action.get_http_query');

        $this->assertInstanceOf('Payum\Bundle\PayumBundle\Action\GetHttpQueryAction', $service);
    }
} 