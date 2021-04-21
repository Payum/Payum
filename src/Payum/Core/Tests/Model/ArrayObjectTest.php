<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\ArrayObject;
use PHPUnit\Framework\TestCase;

class ArrayObjectTest extends TestCase
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

        $this->assertArrayHasKey('foo', $model);
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfElementNotSetOnIsset()
    {
        $model = new ArrayObject();

        $this->assertArrayNotHasKey('foo', $model);
    }

    /**
     * @test
     */
    public function shouldAllowUnsetElement()
    {
        $model = new ArrayObject();

        $model['foo'] = 'theFoo';

        //guard
        $this->assertArrayHasKey('foo', $model);

        unset($model['foo']);

        $this->assertArrayNotHasKey('foo', $model);
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
