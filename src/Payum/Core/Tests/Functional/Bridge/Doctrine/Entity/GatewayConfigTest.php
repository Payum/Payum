<?php
namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Entity;

use Payum\Core\Tests\Functional\Bridge\Doctrine\OrmTest;
use Payum\Core\Tests\Mocks\Entity\GatewayConfig;

class GatewayConfigTest extends OrmTest
{
    public function testShouldAllowPersistWithSomeFieldsSet()
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setGatewayName('fooGateway');
        $gatewayConfig->setFactoryName('fooGatewayFactory');
        $gatewayConfig->setConfig(array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ));

        $this->em->persist($gatewayConfig);
        $this->em->flush();

        $this->assertSame([$gatewayConfig], $this->em->getRepository(GatewayConfig::class)->findAll());
    }

    public function testShouldAllowFindPersistedGatewayConfig()
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setGatewayName('fooGateway');
        $gatewayConfig->setFactoryName('fooGatewayFactory');
        $gatewayConfig->setConfig(array());

        $this->em->persist($gatewayConfig);
        $this->em->flush();

        $id = $gatewayConfig->getId();

        $this->em->clear();

        $foundGatewayConfig = $this->em->find(get_class($gatewayConfig), $id);

        //guard
        $this->assertNotSame($gatewayConfig, $foundGatewayConfig);

        $this->assertSame($gatewayConfig->getId(), $foundGatewayConfig->getId());
    }
}
