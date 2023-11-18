<?php

namespace Payum\Be2Bill\Tests;

use Payum\Be2Bill\Be2BillDirectGatewayFactory;
use Payum\Core\Exception\LogicException;

use Payum\Core\Tests\AbstractGatewayFactoryTest;

class Be2BillDirectGatewayFactoryTest extends AbstractGatewayFactoryTest
{
    public function testShouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig(): void
    {
        $factory = new Be2BillDirectGatewayFactory([
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ]);

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('foo', $config);
        $this->assertSame('fooVal', $config['foo']);

        $this->assertArrayHasKey('bar', $config);
        $this->assertSame('barVal', $config['bar']);
    }

    public function testShouldConfigContainDefaultOptions(): void
    {
        $factory = new Be2BillDirectGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals([
            'identifier' => '',
            'password' => '',
            'sandbox' => true,
        ], $config['payum.default_options']);
    }

    public function testShouldConfigContainFactoryNameAndTitle(): void
    {
        $factory = new Be2BillDirectGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertSame('be2bill_direct', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertSame('Be2Bill Direct', $config['payum.factory_title']);
    }

    public function testShouldThrowIfRequiredOptionsNotPassed(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The identifier, password fields are required.');
        $factory = new Be2BillDirectGatewayFactory();

        $factory->create();
    }

    protected function getGatewayFactoryClass(): string
    {
        return Be2BillDirectGatewayFactory::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getRequiredOptions(): array
    {
        return [
            'identifier' => 'anId',
            'password' => 'aPass',
        ];
    }
}
