<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\ArrayObject;

class ArrayObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementArrayAccessInterface()
    {
        $rc = new \ReflectionClass(ArrayObject::class);

        $this->assertTrue($rc->implementsInterface(\ArrayAccess::class));
    }

    /**
     * @test
     */
    public function shouldImplementIteratorAggregateInterface()
    {
        $rc = new \ReflectionClass(ArrayObject::class);

        $this->assertTrue($rc->implementsInterface(\IteratorAggregate::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new ArrayObject();
    }

    /**
     * @test
     */
    public function shouldAllowAddElementToArray()
    {
        $model = new ArrayObject();

        $model['foo'] = 'theFoo';

        $this->assertEquals('theFoo', $model['foo']);
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfElementSetOnIsset()
    {
        $model = new ArrayObject();

        $model['foo'] = 'theFoo';

        $this->assertTrue(isset($model['foo']));
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfElementNotSetOnIsset()
    {
        $model = new ArrayObject();

        $this->assertFalse(isset($model['foo']));
    }

    /**
     * @test
     */
    public function shouldAllowUnsetElement()
    {
        $model = new ArrayObject();

        $model['foo'] = 'theFoo';

        //guard
        $this->assertTrue(isset($model['foo']));

        unset($model['foo']);

        $this->assertFalse(isset($model['foo']));
    }

    /**
     * @test
     */
    public function shouldReturnArrayIteratorOnGetIterator()
    {
        $model = new ArrayObject();

        $model['foo'] = 'theFoo';
        $model['bar'] = 'theBar';

        $iterator = $model->getIterator();

        $this->assertInstanceOf('ArrayIterator', $iterator);
        $this->assertEquals(
            array(
                'foo' => 'theFoo',
                'bar' => 'theBar',
            ),
            iterator_to_array($model)
        );
    }
}
