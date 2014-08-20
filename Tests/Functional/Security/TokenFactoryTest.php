<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\Security;

use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;

class TokenFactoryTest extends WebTestCase
{
    /**
     * @test
     */
    public function shouldAllowGetAsServiceFromContainer()
    {
        $service = $this->container->get('payum.security.token_factory');

        $this->assertInstanceOf('Payum\Core\Bridge\Symfony\Security\TokenFactory', $service);
    }
} 