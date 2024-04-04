<?php
namespace Payum\Paypal\ProHosted\Nvp\Tests;

use Payum\Core\Tests\AbstractGatewayFactoryTest;
use Payum\Paypal\ProHosted\Nvp\PaypalProHostedGatewayFactory;

class PaypalProHostedGatewayFactoryTest extends AbstractGatewayFactoryTest
{
    protected function getGatewayFactoryClass(): string
    {
        return PaypalProHostedGatewayFactory::class;
    }

    protected function getRequiredOptions(): array
    {
        return [
            'business' => 'aBusiness',
            'username' => 'aName',
            'password' => 'aPass',
            'signature' => 'aSign',
        ];
    }

    public function testShouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig()
    {
        $factory = new PaypalProHostedGatewayFactory(array(
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
        $factory = new PaypalProHostedGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.default_options', $config);

        $this->assertEquals(
            array('username' => '', 'password' => '', 'signature' => '', 'business' => '', 'sandbox' => true),
            $config['payum.default_options']
        );
    }

    public function testShouldConfigContainFactoryNameAndTitle()
    {
        $factory = new PaypalProHostedGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertSame('paypal_pro_hosted', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertSame('Paypal Pro Hosted', $config['payum.factory_title']);
    }

    public function testShouldThrowIfRequiredOptionsNotPassed()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The username, password, signature fields are required.');
        $factory = new PaypalProHostedGatewayFactory();

        $factory->create();
    }
}
