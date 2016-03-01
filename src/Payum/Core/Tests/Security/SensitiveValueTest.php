<?php
namespace Payum\Core\Tests\Security;

use Payum\Core\Security\SensitiveValue;

class SensitiveValueTest extends \PHPUnit_Framework_TestCase
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
    public function couldBeConstructedWithValue()
    {
        new SensitiveValue('cardNumber');
    }

    /**
     * @test
     */
    public function shouldAllowGetValueSetInConstructorAndErase()
    {
        $expectedValue = 'cardNumber';

        $sensitiveValue = new SensitiveValue($expectedValue);

        $this->assertEquals($expectedValue, $sensitiveValue->get());
        $this->assertNull($sensitiveValue->get());
    }

    /**
     * @test
     */
    public function shouldAllowPeekValueSetInConstructorAndNotErase()
    {
        $expectedValue = 'cardNumber';

        $sensitiveValue = new SensitiveValue($expectedValue);

        $this->assertEquals($expectedValue, $sensitiveValue->peek());
        $this->assertEquals($expectedValue, $sensitiveValue->peek());
    }

    /**
     * @test
     */
    public function shouldAllowEraseValue()
    {
        $expectedValue = 'cardNumber';

        $sensitiveValue = new SensitiveValue($expectedValue);

        //guard
        $this->assertEquals($expectedValue, $sensitiveValue->get());

        $sensitiveValue->erase();

        $this->assertNull($sensitiveValue->get());
        $this->assertAttributeEquals(null, 'value', $sensitiveValue);
    }

    /**
     * @test
     */
    public function shouldNotSerializeValue()
    {
        $sensitiveValue = new SensitiveValue('cardNumber');

        $serializedValue = serialize($sensitiveValue);

        $this->assertEquals('N;', $serializedValue);
        $this->assertNull(unserialize($serializedValue));
    }

    /**
     * @test
     */
    public function shouldReturnEmptyStringOnToString()
    {
        $sensitiveValue = new SensitiveValue('cardNumber');

        $this->assertEquals('', (string) $sensitiveValue);
    }

    /**
     * @test
     */
    public function shouldNotExposeValueWhileEncodingToJson()
    {
        $sensitiveValue = new SensitiveValue('cardNumber');

        $this->assertEquals('null', json_encode($sensitiveValue));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage It is not permitted to close this object.
     */
    public function throwIfTryToCloneValue()
    {
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
