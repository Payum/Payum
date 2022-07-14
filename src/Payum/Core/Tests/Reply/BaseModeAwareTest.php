<?php

namespace Payum\Core\Tests\Reply;

use ArrayObject;
use Iterator;
use Payum\Core\Exception\LogicException;
use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;
use Payum\Core\Reply\BaseModelAware;
use Payum\Core\Reply\ReplyInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class BaseModeAwareTest extends TestCase
{
    public function testShouldImplementReplyInterface(): void
    {
        $rc = new ReflectionClass(BaseModelAware::class);

        $this->assertTrue($rc->implementsInterface(ReplyInterface::class));
    }

    public function testShouldImplementModelAwareInterface(): void
    {
        $rc = new ReflectionClass(BaseModelAware::class);

        $this->assertTrue($rc->implementsInterface(ModelAwareInterface::class));
    }

    public function testShouldImplementModelAggregateInterface(): void
    {
        $rc = new ReflectionClass(BaseModelAware::class);

        $this->assertTrue($rc->implementsInterface(ModelAggregateInterface::class));
    }

    public function testShouldBeSubClassOfLogicException(): void
    {
        $rc = new ReflectionClass(BaseModelAware::class);

        $this->assertTrue($rc->isSubclassOf(LogicException::class));
    }

    public function testShouldBeAbstractClass(): void
    {
        $rc = new ReflectionClass(BaseModelAware::class);

        $this->assertTrue($rc->isAbstract());
    }

    public static function provideDifferentPhpTypes(): Iterator
    {
        yield 'object' => [new stdClass()];
        yield 'int' => [5];
        yield 'float' => [5.5];
        yield 'string' => ['foo'];
        yield 'boolean' => [false];
        yield 'resource' => [tmpfile()];
    }

    /**
     * @dataProvider provideDifferentPhpTypes
     */
    public function testShouldAllowSetModelAndGetIt($phpType): void
    {
        $request = new class(123321) extends BaseModelAware {
        };

        $request->setModel($phpType);

        $this->assertEquals($phpType, $request->getModel());
    }

    /**
     * @dataProvider provideDifferentPhpTypes
     */
    public function testShouldAllowGetModelSetInConstructor($phpType): void
    {
        $request = new class($phpType) extends BaseModelAware {
        };

        $this->assertEquals($phpType, $request->getModel());
    }

    public function testShouldConvertArrayToArrayObjectInConstructor(): void
    {
        $model = [
            'foo' => 'bar',
        ];

        $request = $this->getMockForAbstractClass(BaseModelAware::class, [$model]);

        $this->assertInstanceOf(ArrayObject::class, $request->getModel());
        $this->assertEquals($model, (array) $request->getModel());
    }

    public function testShouldConvertArrayToArrayObjectSetWithSetter(): void
    {
        $request = $this->getMockForAbstractClass(BaseModelAware::class, [123321]);

        $model = [
            'foo' => 'bar',
        ];

        $request->setModel($model);

        $this->assertInstanceOf(ArrayObject::class, $request->getModel());
        $this->assertEquals($model, (array) $request->getModel());
    }
}
