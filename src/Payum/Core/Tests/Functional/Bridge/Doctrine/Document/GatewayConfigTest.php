<?php
namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Document;

use Payum\Core\Tests\Functional\Bridge\Doctrine\MongoTest;
use Payum\Core\Tests\Mocks\Document\GatewayConfig;

class GatewayConfigTest extends MongoTest
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

        $this->dm->persist($gatewayConfig);
        $this->dm->flush();

        $this->assertSame([$gatewayConfig], $this->dm->getRepository(GatewayConfig::class)->findAll());
    }

    public function testShouldAllowFindPersistedGatewayConfig()
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setGatewayName('fooGateway');
        $gatewayConfig->setFactoryName('fooGatewayFactory');
        $gatewayConfig->setConfig(array());

        $this->dm->persist($gatewayConfig);
        $this->dm->flush();

        $id = $gatewayConfig->getId();

        $this->dm->clear();

        $foundGatewayConfig = $this->dm->find(get_class($gatewayConfig), $id);

        //guard
        $this->assertNotSame($gatewayConfig, $foundGatewayConfig);

        $this->assertEquals($gatewayConfig->getId(), $foundGatewayConfig->getId());
    }

    public function testShouldStoreConfigAsAssocArray()
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setGatewayName('fooGateway');
        $gatewayConfig->setFactoryName('fooGatewayFactory');
        $gatewayConfig->setConfig(array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ));

        $this->dm->persist($gatewayConfig);
        $this->dm->flush();
        $this->dm->refresh($gatewayConfig);

        $this->assertSame(array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ), $gatewayConfig->getConfig());
    }
}
