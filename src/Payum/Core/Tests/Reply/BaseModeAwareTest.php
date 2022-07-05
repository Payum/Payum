<?php

namespace Payum\Core\Tests\Reply;

use Payum\Core\Reply\BaseModelAware;
use PHPUnit\Framework\TestCase;

class BaseModeAwareTest extends TestCase
{
    public function testShouldImplementReplyInterface()
    {
        $rc = new \ReflectionClass(\Payum\Core\Reply\BaseModelAware::class);

        $this->assertTrue($rc->implementsInterface(\Payum\Core\Reply\ReplyInterface::class));
    }

    public function testShouldImplementModelAwareInterface()
    {
        $rc = new \ReflectionClass(\Payum\Core\Reply\BaseModelAware::class);

        $this->assertTrue($rc->implementsInterface(\Payum\Core\Model\ModelAwareInterface::class));
    }

    public function testShouldImplementModelAggregateInterface()
    {
        $rc = new \ReflectionClass(\Payum\Core\Reply\BaseModelAware::class);

        $this->assertTrue($rc->implementsInterface(\Payum\Core\Model\ModelAggregateInterface::class));
    }

    public function testShouldBeSubClassOfLogicException()
    {
        $rc = new \ReflectionClass(\Payum\Core\Reply\BaseModelAware::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Core\Exception\LogicException::class));
    }

    public function testShouldBeAbstractClass()
    {
        $rc = new \ReflectionClass(\Payum\Core\Reply\BaseModelAware::class);

        $this->assertTrue($rc->isAbstract());
    }

    public static function provideDifferentPhpTypes(): \Iterator
    {
        yield 'object' => [new \stdClass()];
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

        $request = $this->getMockForAbstractClass(\Payum\Core\Reply\BaseModelAware::class, [$model]);

        $this->assertInstanceOf(\ArrayObject::class, $request->getModel());
        $this->assertEquals($model, (array) $request->getModel());
    }

    public function testShouldConvertArrayToArrayObjectSetWithSetter()
    {
        $request = $this->getMockForAbstractClass(\Payum\Core\Reply\BaseModelAware::class, [123321]);

        $model = [
            'foo' => 'bar',
        ];

        $request->setModel($model);

        $this->assertInstanceOf(\ArrayObject::class, $request->getModel());
        $this->assertEquals($model, (array) $request->getModel());
    }
}
