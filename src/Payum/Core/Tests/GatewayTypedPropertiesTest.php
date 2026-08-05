<?php

namespace Payum\Core\Tests;

use Payum\Core\Gateway;
use PHPUnit\Framework\TestCase;
use ReflectionNamedType;
use ReflectionProperty;
use TypeError;

class GatewayTypedPropertiesTest extends TestCase
{
    /**
     * @dataProvider provideTypedArrayProperties
     */
    public function testShouldDeclarePropertyAsTypedArray(string $propertyName): void
    {
        $property = new ReflectionProperty(Gateway::class, $propertyName);

        $type = $property->getType();

        $this->assertInstanceOf(ReflectionNamedType::class, $type);
        $this->assertSame('array', $type->getName());
        $this->assertFalse($type->allowsNull());
    }

    /**
     * @dataProvider provideTypedArrayProperties
     */
    public function testShouldInitialisePropertyToAnEmptyArray(string $propertyName): void
    {
        $property = new ReflectionProperty(Gateway::class, $propertyName);
        $property->setAccessible(true);

        $this->assertSame([], $property->getValue(new Gateway()));
    }

    /**
     * @dataProvider provideTypedArrayProperties
     */
    public function testThrowsWhenPropertyIsSetToANonArrayValue(string $propertyName): void
    {
        $property = new ReflectionProperty(Gateway::class, $propertyName);
        $property->setAccessible(true);

        $this->expectException(TypeError::class);

        $property->setValue(new Gateway(), 'notAnArray');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideTypedArrayProperties(): iterable
    {
        yield 'actions' => ['actions'];

        yield 'stack' => ['stack'];
    }
}
