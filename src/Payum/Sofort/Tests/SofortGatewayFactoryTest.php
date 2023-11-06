<?php
namespace Payum\Sofort\Tests;

use Payum\Core\Tests\AbstractGatewayFactoryTest;
use Payum\Sofort\SofortGatewayFactory;

class SofortGatewayFactoryTest extends AbstractGatewayFactoryTest
{
    protected function getGatewayFactoryClass(): string
    {
        return SofortGatewayFactory::class;
    }

    protected function getRequiredOptions(): array
    {
        return [
            'config_key' => 'foo:bar:baz',
        ];
    }

    public function testShouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig()
    {
        $factory = new SofortGatewayFactory([
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

    public function testShouldConfigContainDefaultOptions()
    {
        $factory = new SofortGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals(
            ['config_key' => '', 'abort_url' => '', 'disable_notification' => false],
            $config['payum.default_options']
        );
    }

    public function testShouldConfigContainFactoryNameAndTitle()
    {
        $factory = new SofortGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertSame('sofort', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertSame('Sofort', $config['payum.factory_title']);
    }

    public function testShouldThrowIfRequiredOptionsNotPassed()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The config_key fields are required.');
        $factory = new SofortGatewayFactory();

        $factory->create();
    }
}
