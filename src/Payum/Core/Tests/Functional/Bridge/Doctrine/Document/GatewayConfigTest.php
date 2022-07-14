<?php

namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Document;

use Payum\Core\Tests\Functional\Bridge\Doctrine\MongoTest;
use Payum\Core\Tests\Mocks\Document\GatewayConfig;

class GatewayConfigTest extends MongoTest
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

        $this->dm->persist($gatewayConfig);
        $this->dm->flush();

        $this->assertSame([$gatewayConfig], $this->dm->getRepository(GatewayConfig::class)->findAll());
    }

    public function testShouldAllowFindPersistedGatewayConfig(): void
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setGatewayName('fooGateway');
        $gatewayConfig->setFactoryName('fooGatewayFactory');
        $gatewayConfig->setConfig([]);

        $this->dm->persist($gatewayConfig);
        $this->dm->flush();

        $id = $gatewayConfig->getId();

        $this->dm->clear();

        $foundGatewayConfig = $this->dm->find(get_class($gatewayConfig), $id);

        //guard
        $this->assertNotSame($gatewayConfig, $foundGatewayConfig);

        $this->assertEquals($gatewayConfig->getId(), $foundGatewayConfig->getId());
    }

    public function testShouldStoreConfigAsAssocArray(): void
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setGatewayName('fooGateway');
        $gatewayConfig->setFactoryName('fooGatewayFactory');
        $gatewayConfig->setConfig([
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ]);

        $this->dm->persist($gatewayConfig);
        $this->dm->flush();
        $this->dm->refresh($gatewayConfig);

        $this->assertEquals([
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ], $gatewayConfig->getConfig());
    }
}
