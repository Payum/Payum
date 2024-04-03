<?php

namespace Payum\AuthorizeNet\Aim\Tests;

use Payum\AuthorizeNet\Aim\AuthorizeNetAimGatewayFactory;
use Payum\Core\Exception\LogicException;
use Payum\Core\Tests\AbstractGatewayFactoryTest;

class AuthorizeNetAimGatewayFactoryTest extends AbstractGatewayFactoryTest
{
    public function testShouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig(): void
    {
        $factory = new AuthorizeNetAimGatewayFactory([
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
        $factory = new AuthorizeNetAimGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals([
            'login_id' => '',
            'transaction_key' => '',
            'sandbox' => true,
        ], $config['payum.default_options']);
    }

    public function testShouldConfigContainFactoryNameAndTitle(): void
    {
        $factory = new AuthorizeNetAimGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertSame('authorize_net_aim', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertSame('Authorize.NET AIM', $config['payum.factory_title']);
    }

    public function testShouldThrowIfRequiredOptionsNotPassed(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The login_id, transaction_key fields are required.');
        $factory = new AuthorizeNetAimGatewayFactory();

        $factory->create();
    }

    protected function getGatewayFactoryClass(): string
    {
        return AuthorizeNetAimGatewayFactory::class;
    }

    protected function getRequiredOptions(): array
    {
        return [
            'login_id' => 'aLoginId',
            'transaction_key' => 'aTransKey',
        ];
    }
}
