<?php
namespace Payum\Core\Tests\Bridge\Spl;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Security\SensitiveValue;
use PHPUnit\Framework\TestCase;

class ArrayObjectTest extends TestCase
{
    public function testShouldBeSubClassOfArrayObject()
    {
        $rc = new \ReflectionClass(ArrayObject::class);

        $this->assertTrue($rc->isSubclassOf('ArrayObject'));
    }

    public function testShouldAllowGetPreviouslySetValueByIndex()
    {
        $array = new ArrayObject();
        $array['foo'] = 'bar';

        $this->assertArrayHasKey('foo', $array);
        $this->assertSame('bar', $array['foo']);
    }

    public function testShouldAllowGetValueSetInInternalArrayObject()
    {
        $internalArray = new \ArrayObject();
        $internalArray['foo'] = 'bar';

        $array = new ArrayObject($internalArray);

        $this->assertArrayHasKey('foo', $array);
        $this->assertSame('bar', $array['foo']);
    }

    public function testShouldAllowGetNullIfValueWithIndexNotSet()
    {
        $array = new ArrayObject();

        $this->assertArrayNotHasKey('foo', $array);
        $this->assertNull($array['foo']);
    }

    public function testShouldReplaceFromArray()
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

        $this->assertSame($expectedArray, (array) $array);
    }

    public function testShouldReplaceFromTraversable()
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

        $this->assertSame($expectedArray, (array) $array);
    }

    public function testThrowIfInvalidArgumentGivenForReplace()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid input given. Should be an array or instance of \Traversable');
        $array = new ArrayObject();

        $array->replace('foo');
    }

    public function testShouldAllowCastToArrayFromCustomArrayObject()
    {
        $input = new CustomArrayObject();
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);

        $array = (array) $arrayObject;

        $this->assertIsArray($array);
        $this->assertSame(array('foo' => 'barbaz'), $array);
    }

    public function testShouldAllowSetToCustomArrayObject()
    {
        $input = new CustomArrayObject();
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);
        $arrayObject['foo'] = 'ololo';

        $this->assertSame('ololo', $input['foo']);
    }

    public function testShouldAllowUnsetToCustomArrayObject()
    {
        $input = new CustomArrayObject();
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);
        unset($arrayObject['foo']);

        $this->assertNull($input['foo']);
    }

    public function testShouldAllowGetValueFromCustomArrayObject()
    {
        $input = new CustomArrayObject();
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);

        $this->assertSame('barbaz', $arrayObject['foo']);
    }

    public function testShouldAllowIssetValueFromCustomArrayObject()
    {
        $input = new CustomArrayObject();
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);

        $this->assertArrayHasKey('foo', $arrayObject);
        $this->assertArrayNotHasKey('bar', $arrayObject);
    }

    public function testShouldAllowIterateOverCustomArrayObject()
    {
        $input = new CustomArrayObject();
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);

        $array = iterator_to_array($arrayObject);

        $this->assertIsArray($array);
        $this->assertSame(array('foo' => 'barbaz'), $array);
    }

    public function testThrowIfRequiredFieldEmptyAndThrowOnInvalidTrue()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The aRequiredField fields are required.');
        $arrayObject = new ArrayObject();

        $arrayObject->validateNotEmpty(array('aRequiredField'), $throwOnInvalid = true);
    }

    public function testThrowIfSecondRequiredFieldEmptyAndThrowOnInvalidTrue()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The otherRequiredField fields are required.');
        $arrayObject = new ArrayObject();
        $arrayObject['aRequiredField'] = 'foo';

        $arrayObject->validateNotEmpty(array('aRequiredField', 'otherRequiredField'), $throwOnInvalid = true);
    }

    public function testThrowByDefaultIfRequiredFieldEmpty()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The aRequiredField fields are required.');
        $arrayObject = new ArrayObject();

        $arrayObject->validateNotEmpty(array('aRequiredField'));
    }

    public function testShouldReturnFalseIfRequiredFieldEmptyAndThrowOnInvalidFalse()
    {
        $arrayObject = new ArrayObject();

        $this->assertFalse($arrayObject->validateNotEmpty(array('aRequiredField'), $throwOnInvalid = false));
    }

    public function testShouldAllowValidateScalarWhetherItNotEmpty()
    {
        $arrayObject = new ArrayObject();

        $this->assertFalse($arrayObject->validateNotEmpty('aRequiredField', $throwOnInvalid = false));
    }

    public function testShouldReturnTrueIfRequiredFieldsNotEmpty()
    {
        $arrayObject = new ArrayObject();
        $arrayObject['aRequiredField'] = 'foo';
        $arrayObject['otherRequiredField'] = 'bar';

        $this->assertTrue($arrayObject->validateNotEmpty(array('aRequiredField', 'otherRequiredField')));
    }

    public function testThrowIfRequiredFieldNotSetAndThrowOnInvalidTrue()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The aRequiredField fields is not set.');
        $arrayObject = new ArrayObject();

        $arrayObject->validatedKeysSet(array('aRequiredField'), $throwOnInvalid = true);
    }

    public function testThrowIfSecondRequiredFieldNotSetAndThrowOnInvalidTrue()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The otherRequiredField fields is not set.');
        $arrayObject = new ArrayObject();
        $arrayObject['aRequiredField'] = 'foo';

        $arrayObject->validatedKeysSet(array('aRequiredField', 'otherRequiredField'), $throwOnInvalid = true);
    }

    public function testThrowByDefaultIfRequiredFieldNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The aRequiredField fields is not set.');
        $arrayObject = new ArrayObject();

        $arrayObject->validatedKeysSet(array('aRequiredField'));
    }

    public function testShouldReturnFalseIfRequiredFieldNotSetAndThrowOnInvalidFalse()
    {
        $arrayObject = new ArrayObject();

        $this->assertFalse($arrayObject->validatedKeysSet(array('aRequiredField'), $throwOnInvalid = false));
    }

    public function testShouldAllowValidateScalarNotSet()
    {
        $arrayObject = new ArrayObject();

        $this->assertFalse($arrayObject->validatedKeysSet('aRequiredField', $throwOnInvalid = false));
    }

    public function testShouldReturnTrueIfRequiredFieldsSet()
    {
        $arrayObject = new ArrayObject();
        $arrayObject['aRequiredField'] = 'foo';
        $arrayObject['otherRequiredField'] = 'bar';

        $this->assertTrue($arrayObject->validatedKeysSet(array('aRequiredField', 'otherRequiredField')));
    }

    public function testShouldConvertArrayObjectToPrimitiveArrayMakingSensitiveValueUnsafeAndEraseIt()
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

    public function testShouldAllowSetDefaultValues()
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
        $this->assertSame(['foo' => 'fooVal'], (array) $subArray);
    }

    public function shouldAllowGetArrayAsArrayObjectIfNotSet()
    {
        $array = new ArrayObject();

        $subArray = $array->getArray('foo');

        $this->assertInstanceOf(ArrayObject::class, $subArray);
        $this->assertSame([], (array) $subArray);
    }

    public function shouldAllowToArrayWithoutSensitiveValuesAdnLocal()
    {
        $array = new ArrayObject([
            'local' => 'theLocal',
            'sensitive' => new SensitiveValue('theSens'),
            'foo' => 'fooVal',
        ]);

        $this->assertSame(['foo' => 'fooVal'], $array->toUnsafeArrayWithoutLocal());
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
