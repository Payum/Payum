<?php

namespace Payum\Core\Tests\Model;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Payum\Core\Model\ArrayObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ArrayObjectTest extends TestCase
{
    public function testShouldImplementArrayAccessInterface(): void
    {
        $rc = new ReflectionClass(ArrayObject::class);

        $this->assertTrue($rc->implementsInterface(ArrayAccess::class));
    }

    public function testShouldImplementIteratorAggregateInterface(): void
    {
        $rc = new ReflectionClass(ArrayObject::class);

        $this->assertTrue($rc->implementsInterface(IteratorAggregate::class));
    }

    public function testShouldAllowAddElementToArray(): void
    {
        $model = new ArrayObject();

        $model['foo'] = 'theFoo';

        $this->assertSame('theFoo', $model['foo']);
    }

    public function testShouldReturnTrueIfElementSetOnIsset(): void
    {
        $model = new ArrayObject();

        $model['foo'] = 'theFoo';

        $this->assertArrayHasKey('foo', $model);
    }

    public function testShouldReturnFalseIfElementNotSetOnIsset(): void
    {
        $model = new ArrayObject();

        $this->assertArrayNotHasKey('foo', $model);
    }

    public function testShouldAllowUnsetElement(): void
    {
        $model = new ArrayObject();

        $model['foo'] = 'theFoo';

        //guard
        $this->assertArrayHasKey('foo', $model);

        unset($model['foo']);

        $this->assertArrayNotHasKey('foo', $model);
    }

    public function testShouldReturnArrayIteratorOnGetIterator(): void
    {
        $model = new ArrayObject();

        $model['foo'] = 'theFoo';
        $model['bar'] = 'theBar';

        $iterator = $model->getIterator();

        $this->assertInstanceOf(ArrayIterator::class, $iterator);
        $this->assertEquals(
            [
                'foo' => 'theFoo',
                'bar' => 'theBar',
            ],
            iterator_to_array($model)
        );
    }
}
