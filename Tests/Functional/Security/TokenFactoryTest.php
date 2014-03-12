<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\Security;

use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;

class GetHttpQueryActionTest extends WebTestCase
{
    /**
     * @test
     */
    public function shouldAllowGetAsServiceFromContainer()
    {
        static::createClient();

        $service = static::$kernel->getContainer()->get('payum.security.token_factory');

        $this->assertInstanceOf('Payum\Bundle\PayumBundle\Security\TokenFactory', $service);
    }
} 