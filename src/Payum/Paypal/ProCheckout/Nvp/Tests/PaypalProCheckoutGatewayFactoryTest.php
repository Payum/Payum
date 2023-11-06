<?php
namespace Payum\Paypal\ProCheckout\Nvp\Tests;

use Payum\Core\Tests\AbstractGatewayFactoryTest;
use Payum\Paypal\ProCheckout\Nvp\PaypalProCheckoutGatewayFactory;

class PaypalProCheckoutGatewayFactoryTest extends AbstractGatewayFactoryTest
{
    protected function getGatewayFactoryClass(): string
    {
        return PaypalProCheckoutGatewayFactory::class;
    }

    protected function getRequiredOptions(): array
    {
        return [
            'username' => 'aName',
            'password' => 'aPass',
            'partner' => 'aPartner',
            'vendor' => 'aVendor',
            'tender' => 'aTender',
        ];
    }

    public function testShouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig()
    {
        $factory = new PaypalProCheckoutGatewayFactory(array(
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
        $factory = new PaypalProCheckoutGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals(array('username' => '', 'password' => '', 'partner' => '', 'vendor' => '', 'tender' => '', 'sandbox' => true), $config['payum.default_options']);
    }

    public function testShouldConfigContainFactoryNameAndTitle()
    {
        $factory = new PaypalProCheckoutGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertSame('paypal_pro_checkout_nvp', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertSame('PayPal ProCheckout', $config['payum.factory_title']);
    }

    public function testShouldThrowIfRequiredOptionsNotPassed()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The username, password, partner, vendor, tender fields are required.');
        $factory = new PaypalProCheckoutGatewayFactory();

        $factory->create();
    }
}
