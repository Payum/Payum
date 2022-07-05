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
    public function testShouldImplementReplyInterface()
    {
        $rc = new ReflectionClass(BaseModelAware::class);

        $this->assertTrue($rc->implementsInterface(ReplyInterface::class));
    }

    public function testShouldImplementModelAwareInterface()
    {
        $rc = new ReflectionClass(BaseModelAware::class);

        $this->assertTrue($rc->implementsInterface(ModelAwareInterface::class));
    }

    public function testShouldImplementModelAggregateInterface()
    {
        $rc = new ReflectionClass(BaseModelAware::class);

        $this->assertTrue($rc->implementsInterface(ModelAggregateInterface::class));
    }

    public function testShouldBeSubClassOfLogicException()
    {
        $rc = new ReflectionClass(BaseModelAware::class);

        $this->assertTrue($rc->isSubclassOf(LogicException::class));
    }

    public function testShouldBeAbstractClass()
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
    public function testShouldAllowSetModelAndGetIt($phpType)
    {
        $request = new class(123321) extends BaseModelAware {
        };

        $request->setModel($phpType);

        $this->assertEquals($phpType, $request->getModel());
    }

    /**
     * @dataProvider provideDifferentPhpTypes
     */
    public function testShouldAllowGetModelSetInConstructor($phpType)
    {
        $request = new class($phpType) extends BaseModelAware {
        };

        $this->assertEquals($phpType, $request->getModel());
    }

    public function testShouldConvertArrayToArrayObjectInConstructor()
    {
        $model = [
            'foo' => 'bar',
        ];

        $request = $this->getMockForAbstractClass(BaseModelAware::class, [$model]);

        $this->assertInstanceOf(ArrayObject::class, $request->getModel());
        $this->assertEquals($model, (array) $request->getModel());
    }

    public function testShouldConvertArrayToArrayObjectSetWithSetter()
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
