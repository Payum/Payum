<?php

namespace Payum\Core\Tests\Security;

use Payum\Core\Exception\LogicException;
use Payum\Core\Security\SensitiveValue;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Serializable;

class SensitiveValueTest extends TestCase
{
    public function testShouldBeFinal(): void
    {
        $rc = new ReflectionClass(SensitiveValue::class);

        $this->assertTrue($rc->isFinal());
    }

    public function testShouldImplementSerializableInterface(): void
    {
        $rc = new ReflectionClass(SensitiveValue::class);

        $this->assertTrue($rc->implementsInterface(Serializable::class));
    }

    public function testShouldAllowGetValueSetInConstructorAndErase(): void
    {
        $expectedValue = 'cardNumber';

        $sensitiveValue = new SensitiveValue($expectedValue);

        $this->assertSame($expectedValue, $sensitiveValue->get());
        $this->assertNull($sensitiveValue->get());
    }

    public function testShouldAllowPeekValueSetInConstructorAndNotErase(): void
    {
        $expectedValue = 'cardNumber';

        $sensitiveValue = new SensitiveValue($expectedValue);

        $this->assertSame($expectedValue, $sensitiveValue->peek());
        $this->assertSame($expectedValue, $sensitiveValue->peek());
    }

    public function testShouldAllowEraseValue(): void
    {
        $expectedValue = 'cardNumber';

        $sensitiveValue = new SensitiveValue($expectedValue);

        $this->assertSame($expectedValue, $sensitiveValue->get());

        $sensitiveValue->erase();

        $this->assertNull($sensitiveValue->get());
    }

    public function testShouldNotSerializeValue(): void
    {
        $sensitiveValue = new SensitiveValue('cardNumber');

        $serializedValue = serialize($sensitiveValue);

        if (PHP_VERSION_ID >= 70400) {
            // the object will be unserialized anyway, make sure it's empty
            $this->assertSame('O:34:"Payum\Core\Security\SensitiveValue":0:{}', $serializedValue);
            $this->assertNull(unserialize($serializedValue)->peek());
        } else {
            $this->assertSame('N;', $serializedValue);
            $this->assertNull(unserialize($serializedValue));
        }
    }

    public function testShouldReturnEmptyStringOnToString(): void
    {
        $sensitiveValue = new SensitiveValue('cardNumber');

        $this->assertSame('', (string) $sensitiveValue);
    }

    public function testShouldNotExposeValueWhileEncodingToJson(): void
    {
        $sensitiveValue = new SensitiveValue('cardNumber');

        $this->assertSame('null', json_encode($sensitiveValue, JSON_THROW_ON_ERROR));
    }

    public function testThrowIfTryToCloneValue(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('It is not permitted to close this object.');
        $sensitiveValue = new SensitiveValue('cardNumber');

        clone $sensitiveValue;
    }

    public function testShouldReturnNewInstanceOfSensitiveValueOnEnsureSensitive(): void
    {
        $this->assertInstanceOf(SensitiveValue::class, SensitiveValue::ensureSensitive('foo'));
    }

    public function testShouldReturnSameInstanceOfSensitiveValueGivenAsArgumentOnEnsureSensitive(): void
    {
        $foo = new SensitiveValue('foo');

        $this->assertSame($foo, SensitiveValue::ensureSensitive($foo));
    }
}
