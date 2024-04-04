<?php
namespace Payum\Core\Tests\Security;

use Payum\Core\Security\SensitiveValue;
use PHPUnit\Framework\TestCase;

class SensitiveValueTest extends TestCase
{
    public function testShouldBeFinal()
    {
        $rc = new \ReflectionClass(SensitiveValue::class);

        $this->assertTrue($rc->isFinal());
    }

    public function testShouldImplementSerializableInterface()
    {
        $rc = new \ReflectionClass(SensitiveValue::class);

        $this->assertTrue($rc->implementsInterface('Serializable'));
    }

    public function testShouldAllowGetValueSetInConstructorAndErase()
    {
        $expectedValue = 'cardNumber';

        $sensitiveValue = new SensitiveValue($expectedValue);

        $this->assertSame($expectedValue, $sensitiveValue->get());
        $this->assertNull($sensitiveValue->get());
    }

    public function testShouldAllowPeekValueSetInConstructorAndNotErase()
    {
        $expectedValue = 'cardNumber';

        $sensitiveValue = new SensitiveValue($expectedValue);

        $this->assertSame($expectedValue, $sensitiveValue->peek());
        $this->assertSame($expectedValue, $sensitiveValue->peek());
    }

    public function testShouldAllowEraseValue()
    {
        $expectedValue = 'cardNumber';

        $sensitiveValue = new SensitiveValue($expectedValue);

        $this->assertSame($expectedValue, $sensitiveValue->get());

        $sensitiveValue->erase();

        $this->assertNull($sensitiveValue->get());
    }

    public function testShouldNotSerializeValue()
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

    public function testShouldReturnEmptyStringOnToString()
    {
        $sensitiveValue = new SensitiveValue('cardNumber');

        $this->assertSame('', (string) $sensitiveValue);
    }

    public function testShouldNotExposeValueWhileEncodingToJson()
    {
        $sensitiveValue = new SensitiveValue('cardNumber');

        $this->assertSame('null', json_encode($sensitiveValue));
    }

    public function testThrowIfTryToCloneValue()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('It is not permitted to close this object.');
        $sensitiveValue = new SensitiveValue('cardNumber');

        clone $sensitiveValue;
    }

    public function testShouldReturnNewInstanceOfSensitiveValueOnEnsureSensitive()
    {
        $this->assertInstanceOf(SensitiveValue::class, SensitiveValue::ensureSensitive('foo'));
    }

    public function testShouldReturnSameInstanceOfSensitiveValueGivenAsArgumentOnEnsureSensitive()
    {
        $foo = new SensitiveValue('foo');

        $this->assertSame($foo, SensitiveValue::ensureSensitive($foo));
    }
}
