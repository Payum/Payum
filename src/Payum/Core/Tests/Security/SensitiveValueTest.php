<?php

namespace Payum\Core\Tests\Security;

use Payum\Core\Security\SensitiveValue;
use PHPUnit\Framework\TestCase;

class SensitiveValueTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeFinal()
    {
        $rc = new \ReflectionClass(SensitiveValue::class);

        $this->assertTrue($rc->isFinal());
    }

    /**
     * @test
     */
    public function shouldImplementSerializableInterface()
    {
        $rc = new \ReflectionClass(SensitiveValue::class);

        $this->assertTrue($rc->implementsInterface('Serializable'));
    }

    /**
     * @test
     */
    public function shouldAllowGetValueSetInConstructorAndErase()
    {
        $expectedValue = 'cardNumber';

        $sensitiveValue = new SensitiveValue($expectedValue);

        $this->assertSame($expectedValue, $sensitiveValue->get());
        $this->assertNull($sensitiveValue->get());
    }

    /**
     * @test
     */
    public function shouldAllowPeekValueSetInConstructorAndNotErase()
    {
        $expectedValue = 'cardNumber';

        $sensitiveValue = new SensitiveValue($expectedValue);

        $this->assertSame($expectedValue, $sensitiveValue->peek());
        $this->assertSame($expectedValue, $sensitiveValue->peek());
    }

    /**
     * @test
     */
    public function shouldAllowEraseValue()
    {
        $expectedValue = 'cardNumber';

        $sensitiveValue = new SensitiveValue($expectedValue);

        $this->assertSame($expectedValue, $sensitiveValue->get());

        $sensitiveValue->erase();

        $this->assertNull($sensitiveValue->get());
    }

    /**
     * @test
     */
    public function shouldNotSerializeValue()
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

    /**
     * @test
     */
    public function shouldReturnEmptyStringOnToString()
    {
        $sensitiveValue = new SensitiveValue('cardNumber');

        $this->assertSame('', (string) $sensitiveValue);
    }

    /**
     * @test
     */
    public function shouldNotExposeValueWhileEncodingToJson()
    {
        $sensitiveValue = new SensitiveValue('cardNumber');

        $this->assertSame('null', json_encode($sensitiveValue));
    }

    /**
     * @test
     */
    public function throwIfTryToCloneValue()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('It is not permitted to close this object.');
        $sensitiveValue = new SensitiveValue('cardNumber');

        clone $sensitiveValue;
    }

    /**
     * @test
     */
    public function shouldReturnNewInstanceOfSensitiveValueOnEnsureSensitive()
    {
        $this->assertInstanceOf(SensitiveValue::class, SensitiveValue::ensureSensitive('foo'));
    }

    /**
     * @test
     */
    public function shouldReturnSameInstanceOfSensitiveValueGivenAsArgumentOnEnsureSensitive()
    {
        $foo = new SensitiveValue('foo');

        $this->assertSame($foo, SensitiveValue::ensureSensitive($foo));
    }
}
