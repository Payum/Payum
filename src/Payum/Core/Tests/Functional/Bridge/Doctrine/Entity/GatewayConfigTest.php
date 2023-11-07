<?php

namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Entity;

use Payum\Core\Tests\Functional\Bridge\Doctrine\OrmTest;
use Payum\Core\Tests\Mocks\Entity\GatewayConfig;

class GatewayConfigTest extends OrmTest
{
    public function testShouldAllowPersistWithSomeFieldsSet(): void
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setGatewayName('fooGateway');
        $gatewayConfig->setFactoryName('fooGatewayFactory');
        $gatewayConfig->setConfig([
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ]);

        $this->em->persist($gatewayConfig);
        $this->em->flush();

        $this->assertSame([$gatewayConfig], $this->em->getRepository(GatewayConfig::class)->findAll());
    }

    public function testShouldAllowFindPersistedGatewayConfig(): void
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setGatewayName('fooGateway');
        $gatewayConfig->setFactoryName('fooGatewayFactory');
        $gatewayConfig->setConfig([]);

        $this->em->persist($gatewayConfig);
        $this->em->flush();

        $id = $gatewayConfig->getId();

        $this->em->clear();

        $foundGatewayConfig = $this->em->find($gatewayConfig::class, $id);

        //guard
        $this->assertNotSame($gatewayConfig, $foundGatewayConfig);

        $this->assertSame($gatewayConfig->getId(), $foundGatewayConfig->getId());
    }
}
