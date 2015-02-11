<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\Security;

use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;

class GenericTokenFactoryTest extends WebTestCase
{
    /**
     * @test
     */
    public function shouldAllowGetAsServiceFromContainer()
    {
        $service = $this->container->get('payum.security.token_factory');

        $this->assertInstanceOf('Payum\Core\Security\GenericTokenFactory', $service);
    }
} 