<?php
namespace Payum\Klarna\Invoice\Tests;

use Payum\Klarna\Invoice\KlarnaInvoiceGatewayFactory;
use PHPUnit\Framework\TestCase;

class KlarnaInvoiceGatewayFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSubClassGatewayFactory()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\KlarnaInvoiceGatewayFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\GatewayFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new KlarnaInvoiceGatewayFactory();
    }

    /**
     * @test
     */
    public function shouldCreateCoreGatewayFactoryIfNotPassed()
    {
        $factory = new KlarnaInvoiceGatewayFactory();

        $this->assertAttributeInstanceOf('Payum\Core\CoreGatewayFactory', 'coreGatewayFactory', $factory);
    }

    /**
     * @test
     */
    public function shouldUseCoreGatewayFactoryPassedAsSecondArgument()
    {
        $coreGatewayFactory = $this->createMock('Payum\Core\GatewayFactoryInterface');

        $factory = new KlarnaInvoiceGatewayFactory(array(), $coreGatewayFactory);

        $this->assertAttributeSame($coreGatewayFactory, 'coreGatewayFactory', $factory);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGateway()
    {
        $factory = new KlarnaInvoiceGatewayFactory();

        $gateway = $factory->create(array(
            'eid' => 'aEID',
            'secret' => 'aSecret',
            'country' => 'SV',
            'language' => 'SE',
            'currency' => 'SEK',
        ));

        $this->assertInstanceOf('Payum\Core\Gateway', $gateway);

        $this->assertAttributeNotEmpty('apis', $gateway);
        $this->assertAttributeNotEmpty('actions', $gateway);

        $extensions = $this->readAttribute($gateway, 'extensions');
        $this->assertAttributeNotEmpty('extensions', $extensions);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayWithCustomApi()
    {
        $factory = new KlarnaInvoiceGatewayFactory();

        $gateway = $factory->create(array('payum.api' => new \stdClass()));

        $this->assertInstanceOf('Payum\Core\Gateway', $gateway);

        $this->assertAttributeNotEmpty('apis', $gateway);
        $this->assertAttributeNotEmpty('actions', $gateway);

        $extensions = $this->readAttribute($gateway, 'extensions');
        $this->assertAttributeNotEmpty('extensions', $extensions);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayConfig()
    {
        $factory = new KlarnaInvoiceGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);
    }

    /**
     * @test
     */
    public function shouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig()
    {
        $factory = new KlarnaInvoiceGatewayFactory(array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ));

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('foo', $config);
        $this->assertEquals('fooVal', $config['foo']);

        $this->assertArrayHasKey('bar', $config);
        $this->assertEquals('barVal', $config['bar']);
    }

    /**
     * @test
     */
    public function shouldConfigContainDefaultOptions()
    {
        $factory = new KlarnaInvoiceGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals(
            array('eid' => '', 'secret' => '', 'country' => '', 'language' => '', 'currency' => '', 'sandbox' => true),
            $config['payum.default_options']
        );
    }

    /**
     * @test
     */
    public function shouldConfigContainFactoryNameAndTitle()
    {
        $factory = new KlarnaInvoiceGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertEquals('klarna_invoice', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertEquals('Klarna Invoice', $config['payum.factory_title']);
    }

    /**
     * @test
     */
    public function shouldThrowIfRequiredOptionsNotPassed()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The eid, secret, country, language, currency fields are required.');
        $factory = new KlarnaInvoiceGatewayFactory();

        $factory->create();
    }
}
