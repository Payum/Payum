<?php
namespace Payum\Core\Tests\Bridge\Spl;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Security\SensitiveValue;
use PHPUnit\Framework\TestCase;

class ArrayObjectTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfArrayObject()
    {
        $rc = new \ReflectionClass(ArrayObject::class);

        $this->assertTrue($rc->isSubclassOf('ArrayObject'));
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetValueByIndex()
    {
        $array = new ArrayObject();
        $array['foo'] = 'bar';

        $this->assertArrayHasKey('foo', $array);
        $this->assertSame('bar', $array['foo']);
    }

    /**
     * @test
     */
    public function shouldAllowGetValueSetInInternalArrayObject()
    {
        $internalArray = new \ArrayObject();
        $internalArray['foo'] = 'bar';

        $array = new ArrayObject($internalArray);

        $this->assertArrayHasKey('foo', $array);
        $this->assertSame('bar', $array['foo']);
    }

    /**
     * @test
     */
    public function shouldAllowGetNullIfValueWithIndexNotSet()
    {
        $array = new ArrayObject();

        $this->assertArrayNotHasKey('foo', $array);
        $this->assertNull($array['foo']);
    }

    /**
     * @test
     */
    public function shouldReplaceFromArray()
    {
        $expectedArray = array(
            'foo' => 'valNew',
            'ololo' => 'valCurr',
            'baz' => 'bazNew',
        );

        $array = new ArrayObject(array('foo' => 'valCurr', 'ololo' => 'valCurr'));

        $array->replace(array(
            'foo' => 'valNew',
            'baz' => 'bazNew',
        ));

        $this->assertEquals($expectedArray, (array) $array);
    }

    /**
     * @test
     */
    public function shouldReplaceFromTraversable()
    {
        $traversable = new \ArrayIterator(array(
            'foo' => 'valNew',
            'baz' => 'bazNew',
        ));

        //guard
        $this->assertInstanceOf('Traversable', $traversable);

        $expectedArray = array(
            'foo' => 'valNew',
            'ololo' => 'valCurr',
            'baz' => 'bazNew',
        );

        $array = new ArrayObject(array('foo' => 'valCurr', 'ololo' => 'valCurr'));

        $array->replace($traversable);

        $this->assertEquals($expectedArray, (array) $array);
    }

    /**
     * @test
     */
    public function throwIfInvalidArgumentGivenForReplace()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid input given. Should be an array or instance of \Traversable');
        $array = new ArrayObject();

        $array->replace('foo');
    }

    /**
     * @test
     */
    public function shouldAllowCastToArrayFromCustomArrayObject()
    {
        $input = new CustomArrayObject();
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);

        $array = (array) $arrayObject;

        $this->assertIsArray($array);
        $this->assertEquals(array('foo' => 'barbaz'), $array);
    }

    /**
     * @test
     */
    public function shouldAllowSetToCustomArrayObject()
    {
        $input = new CustomArrayObject();
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);
        $arrayObject['foo'] = 'ololo';

        $this->assertSame('ololo', $input['foo']);
    }

    /**
     * @test
     */
    public function shouldAllowUnsetToCustomArrayObject()
    {
        $input = new CustomArrayObject();
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);
        unset($arrayObject['foo']);

        $this->assertNull($input['foo']);
    }

    /**
     * @test
     */
    public function shouldAllowGetValueFromCustomArrayObject()
    {
        $input = new CustomArrayObject();
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);

        $this->assertSame('barbaz', $arrayObject['foo']);
    }

    /**
     * @test
     */
    public function shouldAllowIssetValueFromCustomArrayObject()
    {
        $input = new CustomArrayObject();
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);

        $this->assertArrayHasKey('foo', $arrayObject);
        $this->assertArrayNotHasKey('bar', $arrayObject);
    }

    /**
     * @test
     */
    public function shouldAllowIterateOverCustomArrayObject()
    {
        $input = new CustomArrayObject();
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);

        $array = iterator_to_array($arrayObject);

        $this->assertIsArray($array);
        $this->assertEquals(array('foo' => 'barbaz'), $array);
    }

    /**
     * @test
     */
    public function throwIfRequiredFieldEmptyAndThrowOnInvalidTrue()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The aRequiredField fields are required.');
        $arrayObject = new ArrayObject();

        $arrayObject->validateNotEmpty(array('aRequiredField'), $throwOnInvalid = true);
    }

    /**
     * @test
     */
    public function throwIfSecondRequiredFieldEmptyAndThrowOnInvalidTrue()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The otherRequiredField fields are required.');
        $arrayObject = new ArrayObject();
        $arrayObject['aRequiredField'] = 'foo';

        $arrayObject->validateNotEmpty(array('aRequiredField', 'otherRequiredField'), $throwOnInvalid = true);
    }

    /**
     * @test
     */
    public function throwByDefaultIfRequiredFieldEmpty()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The aRequiredField fields are required.');
        $arrayObject = new ArrayObject();

        $arrayObject->validateNotEmpty(array('aRequiredField'));
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfRequiredFieldEmptyAndThrowOnInvalidFalse()
    {
        $arrayObject = new ArrayObject();

        $this->assertFalse($arrayObject->validateNotEmpty(array('aRequiredField'), $throwOnInvalid = false));
    }

    /**
     * @test
     */
    public function shouldAllowValidateScalarWhetherItNotEmpty()
    {
        $arrayObject = new ArrayObject();

        $this->assertFalse($arrayObject->validateNotEmpty('aRequiredField', $throwOnInvalid = false));
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfRequiredFieldsNotEmpty()
    {
        $arrayObject = new ArrayObject();
        $arrayObject['aRequiredField'] = 'foo';
        $arrayObject['otherRequiredField'] = 'bar';

        $this->assertTrue($arrayObject->validateNotEmpty(array('aRequiredField', 'otherRequiredField')));
    }

    /**
     * @test
     */
    public function throwIfRequiredFieldNotSetAndThrowOnInvalidTrue()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The aRequiredField fields is not set.');
        $arrayObject = new ArrayObject();

        $arrayObject->validatedKeysSet(array('aRequiredField'), $throwOnInvalid = true);
    }

    /**
     * @test
     */
    public function throwIfSecondRequiredFieldNotSetAndThrowOnInvalidTrue()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The otherRequiredField fields is not set.');
        $arrayObject = new ArrayObject();
        $arrayObject['aRequiredField'] = 'foo';

        $arrayObject->validatedKeysSet(array('aRequiredField', 'otherRequiredField'), $throwOnInvalid = true);
    }

    /**
     * @test
     */
    public function throwByDefaultIfRequiredFieldNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The aRequiredField fields is not set.');
        $arrayObject = new ArrayObject();

        $arrayObject->validatedKeysSet(array('aRequiredField'));
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfRequiredFieldNotSetAndThrowOnInvalidFalse()
    {
        $arrayObject = new ArrayObject();

        $this->assertFalse($arrayObject->validatedKeysSet(array('aRequiredField'), $throwOnInvalid = false));
    }

    /**
     * @test
     */
    public function shouldAllowValidateScalarNotSet()
    {
        $arrayObject = new ArrayObject();

        $this->assertFalse($arrayObject->validatedKeysSet('aRequiredField', $throwOnInvalid = false));
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfRequiredFieldsSet()
    {
        $arrayObject = new ArrayObject();
        $arrayObject['aRequiredField'] = 'foo';
        $arrayObject['otherRequiredField'] = 'bar';

        $this->assertTrue($arrayObject->validatedKeysSet(array('aRequiredField', 'otherRequiredField')));
    }

    /**
     * @test
     */
    public function shouldConvertArrayObjectToPrimitiveArrayMakingSensitiveValueUnsafeAndEraseIt()
    {
        $sensitiveValue = new SensitiveValue('theCreditCard');

        $arrayObject = new ArrayObject();
        $arrayObject['creditCard'] = $sensitiveValue;
        $arrayObject['email'] = 'bar@example.com';

        $primitiveArray = $arrayObject->toUnsafeArray();

        $this->assertIsArray($primitiveArray);

        $this->assertArrayHasKey('creditCard', $primitiveArray);
        $this->assertSame('theCreditCard', $primitiveArray['creditCard']);

        $this->assertArrayHasKey('email', $primitiveArray);
        $this->assertSame('bar@example.com', $primitiveArray['email']);

        $this->assertNull($sensitiveValue->peek());
    }

    /**
     * @test
     */
    public function shouldAllowSetDefaultValues()
    {
        $arrayObject = new ArrayObject();
        $arrayObject['foo'] = 'fooVal';

        $arrayObject->defaults(array(
            'foo' => 'fooDefVal',
            'bar' => 'barDefVal',
        ));

        $this->assertSame('fooVal', $arrayObject['foo']);
        $this->assertSame('barDefVal', $arrayObject['bar']);
    }

    public function shouldAllowGetArrayAsArrayObjectIfSet()
    {
        $array = new ArrayObject();
        $array['foo'] = ['foo' => 'fooVal'];

        $subArray = $array->getArray('foo');

        $this->assertInstanceOf(ArrayObject::class, $subArray);
        $this->assertEquals(['foo' => 'fooVal'], (array) $subArray);
    }

    public function shouldAllowGetArrayAsArrayObjectIfNotSet()
    {
        $array = new ArrayObject();

        $subArray = $array->getArray('foo');

        $this->assertInstanceOf(ArrayObject::class, $subArray);
        $this->assertEquals([], (array) $subArray);
    }

    public function shouldAllowToArrayWithoutSensitiveValuesAdnLocal()
    {
        $array = new ArrayObject([
            'local' => 'theLocal',
            'sensitive' => new SensitiveValue('theSens'),
            'foo' => 'fooVal',
        ]);

        $this->assertEquals(['foo' => 'fooVal'], $array->toUnsafeArrayWithoutLocal());
    }
}

class CustomArrayObject implements \ArrayAccess, \IteratorAggregate
{
    private $foo;

    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return 'foo' === $offset;
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $this->{$offset} = null;
    }

    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator(array(
            'foo' => $this->foo,
        ));
    }
}
