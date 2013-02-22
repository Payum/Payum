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
}