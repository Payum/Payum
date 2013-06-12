<?php
namespace Payum\Tests\Bridge\Spl;

use Payum\Bridge\Spl\ArrayObject;

class ArrayObjectTest extends \PHPUnit_Framework_TestCase 
{
    /**
     * @test
     */
    public function shouldBeSubClassOfArrayObject()
    {
        $rc = new \ReflectionClass('Payum\Bridge\Spl\ArrayObject');
        
        $this->assertTrue($rc->isSubclassOf('ArrayObject'));
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetValueByIndex()
    {
        $array = new ArrayObject();
        $array['foo'] = 'bar';
        
        $this->assertTrue(isset($array['foo']));
        $this->assertEquals('bar', $array['foo']);
    }

    /**
     * @test
     */
    public function shouldAllowGetValueSetInInternalArrayObject()
    {
        $internalArray = new \ArrayObject;
        $internalArray['foo'] = 'bar';
        
        $array = new ArrayObject($internalArray);

        $this->assertTrue(isset($array['foo']));
        $this->assertEquals('bar', $array['foo']);
    }

    /**
     * @test
     */
    public function shouldAllowGetNullIfValueWithIndexNotSet()
    {
        $array = new ArrayObject();

        $this->assertFalse(isset($array['foo']));
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
            'baz' => 'bazNew'
        );
        
        $array = new ArrayObject(array('foo' => 'valCurr', 'ololo' => 'valCurr'));

        $array->replace(array(
            'foo' => 'valNew',
            'baz' => 'bazNew'
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
            'baz' => 'bazNew'
        ));

        //guard
        $this->assertInstanceOf('Traversable', $traversable);
        
        $expectedArray = array(
            'foo' => 'valNew',
            'ololo' => 'valCurr',
            'baz' => 'bazNew'
        );

        $array = new ArrayObject(array('foo' => 'valCurr', 'ololo' => 'valCurr'));

        $array->replace($traversable);

        $this->assertEquals($expectedArray, (array) $array);
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid input given. Should be an array or instance of \Traversable
     */
    public function throwIfInvalidArgumentGivenForReplace()
    {
        $array = new ArrayObject();

        $array->replace('foo');
    }

    /**
     * @test
     */
    public function shouldAllowCastToArrayFromCustomArrayObject()
    {
        $input = new CustomArrayObject;
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);

        $array = (array) $arrayObject;

        $this->assertInternalType('array', $array);
        $this->assertEquals(array('foo' => 'barbaz'), $array);
    }

    /**
     * @test
     */
    public function shouldAllowSetToCustomArrayObject()
    {
        $input = new CustomArrayObject;
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);
        $arrayObject['foo'] = 'ololo';

        $this->assertEquals('ololo', $input['foo']);
    }

    /**
     * @test
     */
    public function shouldAllowUnsetToCustomArrayObject()
    {
        $input = new CustomArrayObject;
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
        $input = new CustomArrayObject;
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);

        $this->assertEquals('barbaz', $arrayObject['foo']);
    }

    /**
     * @test
     */
    public function shouldAllowIssetValueFromCustomArrayObject()
    {
        $input = new CustomArrayObject;
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);

        $this->assertTrue(isset($arrayObject['foo']));
        $this->assertFalse(isset($arrayObject['bar']));
    }

    /**
     * @test
     */
    public function shouldAllowIterateOverCustomArrayObject()
    {
        $input = new CustomArrayObject;
        $input['foo'] = 'barbaz';

        $arrayObject = new ArrayObject($input);

        $array = iterator_to_array($arrayObject);

        $this->assertInternalType('array', $array);
        $this->assertEquals(array('foo' => 'barbaz'), $array);
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The aRequiredField fields is required.
     */
    public function throwIfRequiredFieldEmptyAndThrowOnInvalidTrue()
    {
        $arrayObject = new ArrayObject();

        $arrayObject->validatedNotEmpty(array('aRequiredField'), $throwOnInvalid = true);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The otherRequiredField fields is required.
     */
    public function throwIfSecondRequiredFieldEmptyAndThrowOnInvalidTrue()
    {
        $arrayObject = new ArrayObject();
        $arrayObject['aRequiredField'] = 'foo';

        $arrayObject->validatedNotEmpty(array('aRequiredField', 'otherRequiredField'), $throwOnInvalid = true);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The aRequiredField fields is required.
     */
    public function throwByDefaultIfRequiredFieldEmpty()
    {
        $arrayObject = new ArrayObject();

        $arrayObject->validatedNotEmpty(array('aRequiredField'));
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfRequiredFieldEmptyAndThrowOnInvalidFalse()
    {
        $arrayObject = new ArrayObject();

        $this->assertFalse($arrayObject->validatedNotEmpty(array('aRequiredField'), $throwOnInvalid = false));
    }

    /**
     * @test
     */
    public function shouldAllowValidateScalarWhetherItNotEmpty()
    {
        $arrayObject = new ArrayObject();

        $this->assertFalse($arrayObject->validatedNotEmpty('aRequiredField', $throwOnInvalid = false));
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfRequiredFieldsNotEmpty()
    {
        $arrayObject = new ArrayObject();
        $arrayObject['aRequiredField'] = 'foo';
        $arrayObject['otherRequiredField'] = 'bar';

        $this->assertTrue($arrayObject->validatedNotEmpty(array('aRequiredField', 'otherRequiredField')));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The aRequiredField fields is not set.
     */
    public function throwIfRequiredFieldNotSetAndThrowOnInvalidTrue()
    {
        $arrayObject = new ArrayObject();

        $arrayObject->validatedKeysSet(array('aRequiredField'), $throwOnInvalid = true);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The otherRequiredField fields is not set.
     */
    public function throwIfSecondRequiredFieldNotSetAndThrowOnInvalidTrue()
    {
        $arrayObject = new ArrayObject();
        $arrayObject['aRequiredField'] = 'foo';

        $arrayObject->validatedKeysSet(array('aRequiredField', 'otherRequiredField'), $throwOnInvalid = true);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The aRequiredField fields is not set.
     */
    public function throwByDefaultIfRequiredFieldNotSet()
    {
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
}

class CustomArrayObject implements \ArrayAccess, \IteratorAggregate
{
    private $foo;
    
    public function offsetExists($offset)
    {
        return 'foo' === $offset;
    }

    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    public function offsetUnset($offset)
    {
        $this->{$offset} = null;
    }
    
    public function getIterator()
    {
        return new \ArrayIterator(array(
            'foo' => $this->foo
        ));
    }
}