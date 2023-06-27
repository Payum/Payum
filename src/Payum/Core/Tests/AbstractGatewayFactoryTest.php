<?php

namespace Payum\Core\Tests;

use Payum\Core\CoreGatewayFactory;
use Payum\Core\Extension\ExtensionCollection;
use Payum\Core\GatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

abstract class AbstractGatewayFactoryTest extends TestCase
{
    public function testFactoryImplementsGatewayFactoryInterface(): void
    {
        $class = $this->getGatewayFactoryClass();
        $this->assertInstanceOf(GatewayFactoryInterface::class, new $class());

        $rc = new ReflectionClass(GatewayFactory::class);

        $this->assertTrue($rc->implementsInterface(GatewayFactoryInterface::class));
    }

    public function testGatewayUsesCoreGatewayFactory(): void
    {
        $class = $this->getGatewayFactoryClass();

        $factory = new $class();

        $this->assertInstanceOf(CoreGatewayFactory::class, $this->getPropertyValue($factory, 'coreGatewayFactory'));
    }

    public function testShouldUseGatewayFactoryPassedAsSecondArgument(): void
    {
        $coreGatewayFactory = $this->createMock(GatewayFactoryInterface::class);

        $class = $this->getGatewayFactoryClass();

        $factory = new $class([], $coreGatewayFactory);

        $ref = new ReflectionProperty($factory, 'coreGatewayFactory');
        $ref->setAccessible(true);
        $this->assertSame($coreGatewayFactory, $ref->getValue($factory));
    }

    public function testShouldAllowCreateGateway(): void
    {
        $class = $this->getGatewayFactoryClass();

        $factory = new $class();

        $this->assertInstanceOf(GatewayFactoryInterface::class, $factory);

        $gateway = $factory->create($this->getRequiredOptions());

        $this->assertNotEmpty($this->getPropertyValue($gateway, 'apis'));
        $this->assertNotEmpty($this->getPropertyValue($gateway, 'actions'));

        $extensions = $this->getPropertyValue($gateway, 'extensions');
        $this->assertInstanceOf(ExtensionCollection::class, $extensions);

        $this->assertNotEmpty($this->getPropertyValue($extensions, 'extensions'));
    }

    public function testShouldAllowCreateGatewayConfig(): void
    {
        $class = $this->getGatewayFactoryClass();

        $factory = new $class();

        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);
    }

    abstract protected function getGatewayFactoryClass(): string;

    /**
     * @return mixed[]
     */
    protected function getRequiredOptions(): array
    {
        return [];
    }

    protected function getPropertyValue(object $object, string $property)
    {
        $ref = new ReflectionProperty($object, $property);
        $ref->setAccessible(true);

        return $ref->getValue($object);
    }
}
