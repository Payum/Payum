<?php
namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Document;

use Payum\Core\Tests\Functional\Bridge\Doctrine\MongoTest;
use Payum\Core\Tests\Mocks\Document\GatewayConfig;

class GatewayConfigTest extends MongoTest
{
    /**
     * @test
     */
    public function shouldAllowPersistWithSomeFieldsSet()
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
    }

    /**
     * @test
     */
    public function shouldAllowFindPersistedGatewayConfig()
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

    /**
     * @test
     */
    public function shouldStoreConfigAsAssocArray()
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

        $this->assertEquals(array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ), $gatewayConfig->getConfig());
    }
}
