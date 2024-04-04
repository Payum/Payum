<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\ArrayObject;
use PHPUnit\Framework\TestCase;

class ArrayObjectTest extends TestCase
{
    public function testShouldImplementArrayAccessInterface()
    {
        $rc = new \ReflectionClass(ArrayObject::class);

        $this->assertTrue($rc->implementsInterface(\ArrayAccess::class));
    }

    public function testShouldImplementIteratorAggregateInterface()
    {
        $rc = new \ReflectionClass(ArrayObject::class);

        $this->assertTrue($rc->implementsInterface(\IteratorAggregate::class));
    }

    public function testShouldAllowAddElementToArray()
    {
        $model = new ArrayObject();

        $model['foo'] = 'theFoo';

        $this->assertSame('theFoo', $model['foo']);
    }

    public function testShouldReturnTrueIfElementSetOnIsset()
    {
        $model = new ArrayObject();

        $model['foo'] = 'theFoo';

        $this->assertArrayHasKey('foo', $model);
    }

    public function testShouldReturnFalseIfElementNotSetOnIsset()
    {
        $model = new ArrayObject();

        $this->assertArrayNotHasKey('foo', $model);
    }

    public function testShouldAllowUnsetElement()
    {
        $model = new ArrayObject();

        $model['foo'] = 'theFoo';

        //guard
        $this->assertArrayHasKey('foo', $model);

        unset($model['foo']);

        $this->assertArrayNotHasKey('foo', $model);
    }

    public function testShouldReturnArrayIteratorOnGetIterator()
    {
        $model = new ArrayObject();

        $model['foo'] = 'theFoo';
        $model['bar'] = 'theBar';

        $iterator = $model->getIterator();

        $this->assertInstanceOf('ArrayIterator', $iterator);
        $this->assertSame(
            array(
                'foo' => 'theFoo',
                'bar' => 'theBar',
            ),
            iterator_to_array($model)
        );
    }
}
