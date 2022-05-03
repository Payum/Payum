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
    public function shouldBeSubClassOfArrayObject(): void
    {
        $rc = new \ReflectionClass(ArrayObject::class);

        $this->assertTrue($rc->isSubclassOf('ArrayObject'));
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetValueByIndex(): void
    {
        $array = new ArrayObject();
        $array['foo'] = 'bar';

        $this->assertArrayHasKey('foo', $array);
        $this->assertEquals('bar', $array['foo']);
    }

    /**
     * @test
     */
    public function shouldAllowGetValueSetInInternalArrayObject(): void
    {
        $internalArray = new \ArrayObject();
        $internalArray['foo'] = 'bar';

        $array = new ArrayObject($internalArray);

        $this->assertArrayHasKey('foo', $array);
        $this->assertEquals('bar', $array['foo']);
    }

    /**
     * @test
     */
    public function shouldAllowGetNullIfValueWithIndexNotSet(): void
    {
        $array = new ArrayObject();

        $this->assertArrayNotHasKey('foo', $array);
        $this->assertNull($array['foo']);
    }

    /**
     * @test
     */
    public function shouldReplaceFromArray(): void
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
    public function shouldReplaceFromTraversable(): void
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
    public function throwIfInvalidArgumentGivenForReplace(): void
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid input given. Should be an array or instance of \Traversable');
        $array = new ArrayObject();

        $array->replace('foo');
    }

    /**
     * @test
     */
    public function shouldAllowCastToArrayFromCustomArrayObject(): void
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
    public function shouldAllowSetToCustomArrayObject(): void
    {
        $input = new CustomArrayObject();
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);
        $arrayObject['foo'] = 'ololo';

        $this->assertEquals('ololo', $input['foo']);
    }

    /**
     * @test
     */
    public function shouldAllowUnsetToCustomArrayObject(): void
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
    public function shouldAllowGetValueFromCustomArrayObject(): void
    {
        $input = new CustomArrayObject();
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);

        $this->assertEquals('barbaz', $arrayObject['foo']);
    }

    /**
     * @test
     */
    public function shouldAllowIssetValueFromCustomArrayObject(): void
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
    public function shouldAllowIterateOverCustomArrayObject(): void
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
    public function throwIfRequiredFieldEmptyAndThrowOnInvalidTrue(): void
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The aRequiredField fields are required.');
        $arrayObject = new ArrayObject();

        $arrayObject->validateNotEmpty(array('aRequiredField'), $throwOnInvalid = true);
    }

    /**
     * @test
     */
    public function throwIfSecondRequiredFieldEmptyAndThrowOnInvalidTrue(): void
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
    public function throwByDefaultIfRequiredFieldEmpty(): void
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The aRequiredField fields are required.');
        $arrayObject = new ArrayObject();

        $arrayObject->validateNotEmpty(array('aRequiredField'));
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfRequiredFieldEmptyAndThrowOnInvalidFalse(): void
    {
        $arrayObject = new ArrayObject();

        $this->assertFalse($arrayObject->validateNotEmpty(array('aRequiredField'), $throwOnInvalid = false));
    }

    /**
     * @test
     */
    public function shouldAllowValidateScalarWhetherItNotEmpty(): void
    {
        $arrayObject = new ArrayObject();

        $this->assertFalse($arrayObject->validateNotEmpty('aRequiredField', $throwOnInvalid = false));
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfRequiredFieldsNotEmpty(): void
    {
        $arrayObject = new ArrayObject();
        $arrayObject['aRequiredField'] = 'foo';
        $arrayObject['otherRequiredField'] = 'bar';

        $this->assertTrue($arrayObject->validateNotEmpty(array('aRequiredField', 'otherRequiredField')));
    }

    /**
     * @test
     */
    public function throwIfRequiredFieldNotSetAndThrowOnInvalidTrue(): void
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The aRequiredField fields is not set.');
        $arrayObject = new ArrayObject();

        $arrayObject->validatedKeysSet(array('aRequiredField'), $throwOnInvalid = true);
    }

    /**
     * @test
     */
    public function throwIfSecondRequiredFieldNotSetAndThrowOnInvalidTrue(): void
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
    public function throwByDefaultIfRequiredFieldNotSet(): void
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The aRequiredField fields is not set.');
        $arrayObject = new ArrayObject();

        $arrayObject->validatedKeysSet(array('aRequiredField'));
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfRequiredFieldNotSetAndThrowOnInvalidFalse(): void
    {
        $arrayObject = new ArrayObject();

        $this->assertFalse($arrayObject->validatedKeysSet(array('aRequiredField'), $throwOnInvalid = false));
    }

    /**
     * @test
     */
    public function shouldAllowValidateScalarNotSet(): void
    {
        $arrayObject = new ArrayObject();

        $this->assertFalse($arrayObject->validatedKeysSet('aRequiredField', $throwOnInvalid = false));
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfRequiredFieldsSet(): void
    {
        $arrayObject = new ArrayObject();
        $arrayObject['aRequiredField'] = 'foo';
        $arrayObject['otherRequiredField'] = 'bar';

        $this->assertTrue($arrayObject->validatedKeysSet(array('aRequiredField', 'otherRequiredField')));
    }

    /**
     * @test
     */
    public function shouldConvertArrayObjectToPrimitiveArrayMakingSensitiveValueUnsafeAndEraseIt(): void
    {
        $sensitiveValue = new SensitiveValue('theCreditCard');

        $arrayObject = new ArrayObject();
        $arrayObject['creditCard'] = $sensitiveValue;
        $arrayObject['email'] = 'bar@example.com';

        $primitiveArray = $arrayObject->toUnsafeArray();

        $this->assertIsArray($primitiveArray);

        $this->assertArrayHasKey('creditCard', $primitiveArray);
        $this->assertEquals('theCreditCard', $primitiveArray['creditCard']);

        $this->assertArrayHasKey('email', $primitiveArray);
        $this->assertEquals('bar@example.com', $primitiveArray['email']);

        $this->assertNull($sensitiveValue->peek());
    }

    /**
     * @test
     */
    public function shouldAllowSetDefaultValues(): void
    {
        $arrayObject = new ArrayObject();
        $arrayObject['foo'] = 'fooVal';

        $arrayObject->defaults(array(
            'foo' => 'fooDefVal',
            'bar' => 'barDefVal',
        ));

        $this->assertEquals('fooVal', $arrayObject['foo']);
        $this->assertEquals('barDefVal', $arrayObject['bar']);
    }

    public function shouldAllowGetArrayAsArrayObjectIfSet(): void
    {
        $array = new ArrayObject();
        $array['foo'] = ['foo' => 'fooVal'];

        $subArray = $array->getArray('foo');

        $this->assertInstanceOf(ArrayObject::class, $subArray);
        $this->assertEquals(['foo' => 'fooVal'], (array) $subArray);
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

        $this->assertEquals(['foo' => 'fooVal'], $array->toUnsafeArrayWithoutLocal());
    }
}

class CustomArrayObject implements \ArrayAccess, \IteratorAggregate
{
    private $foo;

    public function offsetExists(mixed $offset): bool
    {
        return 'foo' === $offset;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->{$offset};
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->{$offset} = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->{$offset} = null;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator([
            'foo' => $this->foo,
        ]);
    }
}
