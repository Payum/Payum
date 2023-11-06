<?php
namespace Payum\Be2Bill\Tests;

use Payum\Be2Bill\Be2BillDirectGatewayFactory;

use Payum\Core\Tests\AbstractGatewayFactoryTest;

class Be2BillDirectGatewayFactoryTest extends AbstractGatewayFactoryTest
{
    protected function getGatewayFactoryClass(): string
    {
        return Be2BillDirectGatewayFactory::class;
    }

    protected function getRequiredOptions(): array
    {
        return [
            'identifier' => 'anId',
            'password' => 'aPass',
        ];
    }

    public function testShouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig()
    {
        $factory = new Be2BillDirectGatewayFactory(array(
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
        $factory = new Be2BillDirectGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals(array('identifier' => '', 'password' => '', 'sandbox' => true), $config['payum.default_options']);
    }

    public function testShouldConfigContainFactoryNameAndTitle()
    {
        $factory = new Be2BillDirectGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertSame('be2bill_direct', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertSame('Be2Bill Direct', $config['payum.factory_title']);
    }

    public function testShouldThrowIfRequiredOptionsNotPassed()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The identifier, password fields are required.');
        $factory = new Be2BillDirectGatewayFactory();

        $factory->create();
    }
}
