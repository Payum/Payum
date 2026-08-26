<?php

declare(strict_types=1);

namespace Payum\Sofort\Tests;

use Payum\Core\CoreGatewayFactory;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayInterface;
use Payum\Core\Tests\AbstractGatewayFactoryTest;
use Payum\Core\Tests\Mocks\Clock\FrozenClock;
use Payum\Sofort\Action\StatusAction;
use Payum\Sofort\SofortGatewayFactory;
use Psr\Clock\ClockInterface;
use ReflectionProperty;

final class SofortGatewayFactoryTest extends AbstractGatewayFactoryTest
{
    public function testShouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig(): void
    {
        $factory = new SofortGatewayFactory([
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ]);

        $config = $factory->createConfig();

        $this->assertArrayHasKey('foo', $config);
        $this->assertSame('fooVal', $config['foo']);

        $this->assertArrayHasKey('bar', $config);
        $this->assertSame('barVal', $config['bar']);
    }

    public function testShouldConfigContainDefaultOptions(): void
    {
        $factory = new SofortGatewayFactory();

        $config = $factory->createConfig();

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals(
            [
                'config_key' => '',
                'abort_url' => '',
                'disable_notification' => false,
            ],
            $config['payum.default_options']
        );
    }

    public function testShouldConfigContainFactoryNameAndTitle(): void
    {
        $factory = new SofortGatewayFactory();

        $config = $factory->createConfig();

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertSame('sofort', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertSame('Sofort', $config['payum.factory_title']);
    }

    public function testShouldThrowIfRequiredOptionsNotPassed(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The config_key fields are required.');
        $factory = new SofortGatewayFactory();

        $factory->create();
    }

    public function testShouldInjectTheContainersClockIntoTheStatusAction(): void
    {
        $clock = new FrozenClock('2026-01-01 12:00:00');

        $factory = new SofortGatewayFactory([], new CoreGatewayFactory([
            ClockInterface::class => $clock,
        ]));

        $action = $this->findStatusAction($factory->create($this->getRequiredOptions()));

        $ref = new ReflectionProperty($action, 'clock');

        $this->assertSame($clock, $ref->getValue($action));
    }

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

    private function findStatusAction(GatewayInterface $gateway): StatusAction
    {
        foreach ($this->getPropertyValue($gateway, 'actions') as $action) {
            if ($action instanceof StatusAction) {
                return $action;
            }
        }

        $this->fail('The gateway has no status action.');
    }
}
