<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests;

use Payum\Core\Tests\AbstractGatewayFactoryTest;
use Payum\Paypal\ExpressCheckout\Nvp\PaypalExpressCheckoutGatewayFactory;

class PaypalExpressCheckoutGatewayFactoryTest extends AbstractGatewayFactoryTest
{
    protected function getGatewayFactoryClass(): string
    {
        return PaypalExpressCheckoutGatewayFactory::class;
    }

    protected function getRequiredOptions(): array
    {
        return [
            'username' => 'aName',
            'password' => 'aPass',
            'signature' => 'aSign',
        ];
    }

    public function testShouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig()
    {
        $factory = new PaypalExpressCheckoutGatewayFactory(array(
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
        $factory = new PaypalExpressCheckoutGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals(
            array('username' => '', 'password' => '', 'signature' => '', 'sandbox' => true),
            $config['payum.default_options']
        );
    }

    public function testShouldConfigContainFactoryNameAndTitle()
    {
        $factory = new PaypalExpressCheckoutGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertSame('paypal_express_checkout_nvp', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertSame('PayPal ExpressCheckout', $config['payum.factory_title']);
    }

    public function testShouldThrowIfRequiredOptionsNotPassed()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The username, password, signature fields are required.');
        $factory = new PaypalExpressCheckoutGatewayFactory();

        $factory->create();
    }

    public function testShouldConfigurePaths()
    {
        $factory = new PaypalExpressCheckoutGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);

        $this->assertIsArray($config['payum.paths']);
        $this->assertNotEmpty($config['payum.paths']);

        $this->assertArrayHasKey('PayumCore', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['PayumCore']);
        $this->assertFileExists($config['payum.paths']['PayumCore']);

        $this->assertArrayHasKey('PayumPaypalExpressCheckout', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['PayumPaypalExpressCheckout']);
        $this->assertFileExists($config['payum.paths']['PayumPaypalExpressCheckout']);
    }
}
