<?php
namespace Payum\Core\Tests\Reply;

use Payum\Core\Reply\BaseModelAware;
use PHPUnit\Framework\TestCase;

class BaseModeAwareTest extends TestCase
{
    public function testShouldImplementReplyInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\BaseModelAware');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Reply\ReplyInterface'));
    }

    public function testShouldImplementModelAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\BaseModelAware');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Model\ModelAwareInterface'));
    }

    public function testShouldImplementModelAggregateInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\BaseModelAware');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Model\ModelAggregateInterface'));
    }

    public function testShouldBeSubClassOfLogicException()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\BaseModelAware');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Exception\LogicException'));
    }

    public function testShouldBeAbstractClass()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\BaseModelAware');

        $this->assertTrue($rc->isAbstract());
    }

    public static function provideDifferentPhpTypes(): \Iterator
    {
        yield 'object' => array(new \stdClass());
        yield 'int' => array(5);
        yield 'float' => array(5.5);
        yield 'string' => array('foo');
        yield 'boolean' => array(false);
        yield 'resource' => array(tmpfile());
    }

    /**
     * @dataProvider provideDifferentPhpTypes
     */
    public function testShouldAllowSetModelAndGetIt($phpType)
    {
        $request = new class(123321) extends BaseModelAware {};

        $request->setModel($phpType);

        $this->assertEquals($phpType, $request->getModel());
    }

    /**
     * @dataProvider provideDifferentPhpTypes
     */
    public function testShouldAllowGetModelSetInConstructor($phpType)
    {
        $request = new class($phpType) extends BaseModelAware {};

        $this->assertEquals($phpType, $request->getModel());
    }

    public function testShouldConvertArrayToArrayObjectInConstructor()
    {
        $model = array('foo' => 'bar');

        $request = $this->getMockForAbstractClass('Payum\Core\Reply\BaseModelAware', array($model));

        $this->assertInstanceOf('ArrayObject', $request->getModel());
        $this->assertSame($model, (array) $request->getModel());
    }

    public function testShouldConvertArrayToArrayObjectSetWithSetter()
    {
        $request = $this->getMockForAbstractClass('Payum\Core\Reply\BaseModelAware', array(123321));

        $model = array('foo' => 'bar');

        $request->setModel($model);

        $this->assertInstanceOf('ArrayObject', $request->getModel());
        $this->assertSame($model, (array) $request->getModel());
    }
}
