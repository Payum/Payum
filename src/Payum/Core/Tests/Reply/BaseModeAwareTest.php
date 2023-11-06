<?php
namespace Payum\Core\Tests\Reply;

use Payum\Core\Reply\BaseModelAware;
use PHPUnit\Framework\TestCase;

class BaseModeAwareTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementReplyInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\BaseModelAware');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Reply\ReplyInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementModelAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\BaseModelAware');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Model\ModelAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementModelAggregateInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\BaseModelAware');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Model\ModelAggregateInterface'));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfLogicException()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\BaseModelAware');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Exception\LogicException'));
    }

    /**
     * @test
     */
    public function shouldBeAbstractClass()
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
     * @test
     *
     * @dataProvider provideDifferentPhpTypes
     */
    public function shouldAllowSetModelAndGetIt($phpType)
    {
        $request = new class(123321) extends BaseModelAware {};

        $request->setModel($phpType);

        $this->assertEquals($phpType, $request->getModel());
    }

    /**
     * @test
     *
     * @dataProvider provideDifferentPhpTypes
     */
    public function shouldAllowGetModelSetInConstructor($phpType)
    {
        $request = new class($phpType) extends BaseModelAware {};

        $this->assertEquals($phpType, $request->getModel());
    }

    /**
     * @test
     */
    public function shouldConvertArrayToArrayObjectInConstructor()
    {
        $model = array('foo' => 'bar');

        $request = $this->getMockForAbstractClass('Payum\Core\Reply\BaseModelAware', array($model));

        $this->assertInstanceOf('ArrayObject', $request->getModel());
        $this->assertSame($model, (array) $request->getModel());
    }

    /**
     * @test
     */
    public function shouldConvertArrayToArrayObjectSetWithSetter()
    {
        $request = $this->getMockForAbstractClass('Payum\Core\Reply\BaseModelAware', array(123321));

        $model = array('foo' => 'bar');

        $request->setModel($model);

        $this->assertInstanceOf('ArrayObject', $request->getModel());
        $this->assertSame($model, (array) $request->getModel());
    }
}
