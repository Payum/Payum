<?php
namespace Payum\Core\Tests;

use Payum\Core\CoreGatewayFactory;
use Payum\Core\Extension\ExtensionCollection;
use Payum\Core\GatewayFactoryInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractGatewayFactoryTest extends TestCase
{
    abstract protected function getGatewayFactoryClass(): string;

    protected function getRequiredOptions(): array
    {
        return [];
    }

    public function testFactoryImplementsGatewayFactoryInterface()
    {
        $class = $this->getGatewayFactoryClass();
        $this->assertInstanceOf(GatewayFactoryInterface::class, new $class);

        $rc = new \ReflectionClass('Payum\Core\GatewayFactory');

        $this->assertTrue($rc->implementsInterface('Payum\Core\GatewayFactoryInterface'));
    }

    public function testGatewayUsesCoreGatewayFactory()
    {
        $class = $this->getGatewayFactoryClass();

        $factory = new $class;

        $this->assertInstanceOf(CoreGatewayFactory::class, $this->getPropertyValue($factory, 'coreGatewayFactory'));
    }

    public function testShouldUseGatewayFactoryPassedAsSecondArgument()
    {
        $coreGatewayFactory = $this->createMock(GatewayFactoryInterface::class);

        $class = $this->getGatewayFactoryClass();

        $factory = new $class(array(), $coreGatewayFactory);

        $ref = new \ReflectionProperty($factory, 'coreGatewayFactory');
        $ref->setAccessible(true);
        $this->assertSame($coreGatewayFactory, $ref->getValue($factory));
    }

    public function testShouldAllowCreateGateway()
    {
        $class = $this->getGatewayFactoryClass();

        $factory = new $class;

        $this->assertInstanceOf(GatewayFactoryInterface::class, $factory);

        $gateway = $factory->create($this->getRequiredOptions());

        $this->assertNotEmpty($this->getPropertyValue($gateway, 'apis'));
        $this->assertNotEmpty($this->getPropertyValue($gateway, 'actions'));

        $extensions = $this->getPropertyValue($gateway, 'extensions');
        $this->assertInstanceOf(ExtensionCollection::class, $extensions);

        $this->assertNotEmpty($this->getPropertyValue($extensions, 'extensions'));
    }

    public function testShouldAllowCreateGatewayConfig()
    {
        $class = $this->getGatewayFactoryClass();

        $factory = new $class;

        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);
    }

    protected function getPropertyValue(object $object, string $property)
    {
        $ref = new \ReflectionProperty($object, $property);
        $ref->setAccessible(true);

        return $ref->getValue($object);
    }
}
