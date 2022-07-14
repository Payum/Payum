<?php

namespace Payum\Core\Tests\Bridge\Spl;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Security\SensitiveValue;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReturnTypeWillChange;
use Traversable;

class ArrayObjectTest extends TestCase
{
    public function testShouldBeSubClassOfArrayObject(): void
    {
        $rc = new ReflectionClass(ArrayObject::class);

        $this->assertTrue($rc->isSubclassOf(\ArrayObject::class));
    }

    public function testShouldAllowGetPreviouslySetValueByIndex(): void
    {
        $array = new ArrayObject();
        $array['foo'] = 'bar';

        $this->assertArrayHasKey('foo', $array);
        $this->assertSame('bar', $array['foo']);
    }

    public function testShouldAllowGetValueSetInInternalArrayObject(): void
    {
        $internalArray = new \ArrayObject();
        $internalArray['foo'] = 'bar';

        $array = new ArrayObject($internalArray);

        $this->assertArrayHasKey('foo', $array);
        $this->assertSame('bar', $array['foo']);
    }

    public function testShouldAllowGetNullIfValueWithIndexNotSet(): void
    {
        $array = new ArrayObject();

        $this->assertArrayNotHasKey('foo', $array);
        $this->assertNull($array['foo']);
    }

    public function testShouldReplaceFromArray(): void
    {
        $expectedArray = [
            'foo' => 'valNew',
            'ololo' => 'valCurr',
            'baz' => 'bazNew',
        ];

        $array = new ArrayObject([
            'foo' => 'valCurr',
            'ololo' => 'valCurr',
        ]);

        $array->replace([
            'foo' => 'valNew',
            'baz' => 'bazNew',
        ]);

        $this->assertEquals($expectedArray, (array) $array);
    }

    public function testShouldReplaceFromTraversable(): void
    {
        $traversable = new ArrayIterator([
            'foo' => 'valNew',
            'baz' => 'bazNew',
        ]);

        //guard
        $this->assertInstanceOf(Traversable::class, $traversable);

        $expectedArray = [
            'foo' => 'valNew',
            'ololo' => 'valCurr',
            'baz' => 'bazNew',
        ];

        $array = new ArrayObject([
            'foo' => 'valCurr',
            'ololo' => 'valCurr',
        ]);

        $array->replace($traversable);

        $this->assertEquals($expectedArray, (array) $array);
    }

    public function testThrowIfInvalidArgumentGivenForReplace(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid input given. Should be an array or instance of \Traversable');
        $array = new ArrayObject();

        $array->replace('foo');
    }

    public function testShouldAllowCastToArrayFromCustomArrayObject(): void
    {
        $input = new CustomArrayObject();
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);

        $array = (array) $arrayObject;

        $this->assertIsArray($array);
        $this->assertEquals([
            'foo' => 'barbaz',
        ], $array);
    }

    public function testShouldAllowSetToCustomArrayObject(): void
    {
        $input = new CustomArrayObject();
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);
        $arrayObject['foo'] = 'ololo';

        $this->assertSame('ololo', $input['foo']);
    }

    public function testShouldAllowUnsetToCustomArrayObject(): void
    {
        $input = new CustomArrayObject();
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);
        unset($arrayObject['foo']);

        $this->assertNull($input['foo']);
    }

    public function testShouldAllowGetValueFromCustomArrayObject(): void
    {
        $input = new CustomArrayObject();
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);

        $this->assertSame('barbaz', $arrayObject['foo']);
    }

    public function testShouldAllowIssetValueFromCustomArrayObject(): void
    {
        $input = new CustomArrayObject();
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);

        $this->assertArrayHasKey('foo', $arrayObject);
        $this->assertArrayNotHasKey('bar', $arrayObject);
    }

    public function testShouldAllowIterateOverCustomArrayObject(): void
    {
        $input = new CustomArrayObject();
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);

        $array = iterator_to_array($arrayObject);

        $this->assertIsArray($array);
        $this->assertEquals([
            'foo' => 'barbaz',
        ], $array);
    }

    public function testThrowIfRequiredFieldEmptyAndThrowOnInvalidTrue(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The aRequiredField fields are required.');
        $arrayObject = new ArrayObject();

        $arrayObject->validateNotEmpty(['aRequiredField'], $throwOnInvalid = true);
    }

    public function testThrowIfSecondRequiredFieldEmptyAndThrowOnInvalidTrue(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The otherRequiredField fields are required.');
        $arrayObject = new ArrayObject();
        $arrayObject['aRequiredField'] = 'foo';

        $arrayObject->validateNotEmpty(['aRequiredField', 'otherRequiredField'], $throwOnInvalid = true);
    }

    public function testThrowByDefaultIfRequiredFieldEmpty(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The aRequiredField fields are required.');
        $arrayObject = new ArrayObject();

        $arrayObject->validateNotEmpty(['aRequiredField']);
    }

    public function testShouldReturnFalseIfRequiredFieldEmptyAndThrowOnInvalidFalse(): void
    {
        $arrayObject = new ArrayObject();

        $this->assertFalse($arrayObject->validateNotEmpty(['aRequiredField'], $throwOnInvalid = false));
    }

    public function testShouldAllowValidateScalarWhetherItNotEmpty(): void
    {
        $arrayObject = new ArrayObject();

        $this->assertFalse($arrayObject->validateNotEmpty('aRequiredField', $throwOnInvalid = false));
    }

    public function testShouldReturnTrueIfRequiredFieldsNotEmpty(): void
    {
        $arrayObject = new ArrayObject();
        $arrayObject['aRequiredField'] = 'foo';
        $arrayObject['otherRequiredField'] = 'bar';

        $this->assertTrue($arrayObject->validateNotEmpty(['aRequiredField', 'otherRequiredField']));
    }

    public function testThrowIfRequiredFieldNotSetAndThrowOnInvalidTrue(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The aRequiredField fields is not set.');
        $arrayObject = new ArrayObject();

        $arrayObject->validatedKeysSet(['aRequiredField'], $throwOnInvalid = true);
    }

    public function testThrowIfSecondRequiredFieldNotSetAndThrowOnInvalidTrue(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The otherRequiredField fields is not set.');
        $arrayObject = new ArrayObject();
        $arrayObject['aRequiredField'] = 'foo';

        $arrayObject->validatedKeysSet(['aRequiredField', 'otherRequiredField'], $throwOnInvalid = true);
    }

    public function testThrowByDefaultIfRequiredFieldNotSet(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The aRequiredField fields is not set.');
        $arrayObject = new ArrayObject();

        $arrayObject->validatedKeysSet(['aRequiredField']);
    }

    public function testShouldReturnFalseIfRequiredFieldNotSetAndThrowOnInvalidFalse(): void
    {
        $arrayObject = new ArrayObject();

        $this->assertFalse($arrayObject->validatedKeysSet(['aRequiredField'], $throwOnInvalid = false));
    }

    public function testShouldAllowValidateScalarNotSet(): void
    {
        $arrayObject = new ArrayObject();

        $this->assertFalse($arrayObject->validatedKeysSet('aRequiredField', $throwOnInvalid = false));
    }

    public function testShouldReturnTrueIfRequiredFieldsSet(): void
    {
        $arrayObject = new ArrayObject();
        $arrayObject['aRequiredField'] = 'foo';
        $arrayObject['otherRequiredField'] = 'bar';

        $this->assertTrue($arrayObject->validatedKeysSet(['aRequiredField', 'otherRequiredField']));
    }

    public function testShouldConvertArrayObjectToPrimitiveArrayMakingSensitiveValueUnsafeAndEraseIt(): void
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

    public function testShouldAllowSetDefaultValues(): void
    {
        $arrayObject = new ArrayObject();
        $arrayObject['foo'] = 'fooVal';

        $arrayObject->defaults([
            'foo' => 'fooDefVal',
            'bar' => 'barDefVal',
        ]);

        $this->assertSame('fooVal', $arrayObject['foo']);
        $this->assertSame('barDefVal', $arrayObject['bar']);
    }

    public function shouldAllowGetArrayAsArrayObjectIfSet(): void
    {
        $array = new ArrayObject();
        $array['foo'] = [
            'foo' => 'fooVal',
        ];

        $subArray = $array->getArray('foo');

        $this->assertInstanceOf(ArrayObject::class, $subArray);
        $this->assertEquals([
            'foo' => 'fooVal',
        ], (array) $subArray);
    }

    public function shouldAllowGetArrayAsArrayObjectIfNotSet(): void
    {
        $array = new ArrayObject();

        $subArray = $array->getArray('foo');

        $this->assertInstanceOf(ArrayObject::class, $subArray);
        $this->assertEquals([], (array) $subArray);
    }

    public function shouldAllowToArrayWithoutSensitiveValuesAdnLocal(): void
    {
        $array = new ArrayObject([
            'local' => 'theLocal',
            'sensitive' => new SensitiveValue('theSens'),
            'foo' => 'fooVal',
        ]);

        $this->assertEquals([
            'foo' => 'fooVal',
        ], $array->toUnsafeArrayWithoutLocal());
    }
}

class CustomArrayObject implements ArrayAccess, IteratorAggregate
{
    private $foo;

    #[ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return 'foo' === $offset;
    }

    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        $this->{$offset} = $value;
    }

    #[ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        $this->{$offset} = null;
    }

    #[ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator([
            'foo' => $this->foo,
        ]);
    }
}
