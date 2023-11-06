<?php
namespace Payum\Klarna\Checkout\Tests;

use Payum\Core\Tests\AbstractGatewayFactoryTest;
use Payum\Klarna\Checkout\KlarnaCheckoutGatewayFactory;

class KlarnaCheckoutGatewayFactoryTest extends AbstractGatewayFactoryTest
{
    protected function getGatewayFactoryClass(): string
    {
        return KlarnaCheckoutGatewayFactory::class;
    }

    protected function getRequiredOptions(): array
    {
        return [
            'merchant_id' => 'aMerchId',
            'secret' => 'aSecret',
        ];
    }

    public function testShouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig()
    {
        $factory = new KlarnaCheckoutGatewayFactory(array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ));

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('foo', $config);
        $this->assertSame('fooVal', $config['foo']);

        $this->assertArrayHasKey('bar', $config);
        $this->assertSame('barVal', $config['bar']);
    }

    public function testShouldConfigContainDefaultOptions()
    {
        $factory = new KlarnaCheckoutGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals(
            array(
                'merchant_id' => '',
                'secret' => '',
                'checkout_uri' => '',
                'terms_uri' => '',
                'sandbox' => true,
            ),
            $config['payum.default_options']
        );
    }

    public function testShouldConfigContainFactoryNameAndTitle()
    {
        $factory = new KlarnaCheckoutGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertSame('klarna_checkout', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertSame('Klarna Checkout', $config['payum.factory_title']);
    }

    public function testShouldThrowIfRequiredOptionsNotPassed()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The merchant_id, secret fields are required.');
        $factory = new KlarnaCheckoutGatewayFactory();

        $factory->create();
    }

    public function testShouldConfigurePaths()
    {
        $factory = new KlarnaCheckoutGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);

        $this->assertIsArray($config['payum.paths']);
        $this->assertNotEmpty($config['payum.paths']);

        $this->assertArrayHasKey('PayumCore', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['PayumCore']);
        $this->assertFileExists($config['payum.paths']['PayumCore']);

        $this->assertArrayHasKey('PayumKlarnaCheckout', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['PayumKlarnaCheckout']);
        $this->assertFileExists($config['payum.paths']['PayumKlarnaCheckout']);
    }
}
