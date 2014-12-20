<?php
namespace Payum\Core\Tests\Reply;

class BaseModeAwareTest extends \PHPUnit_Framework_TestCase
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

    public static function provideDifferentPhpTypes()
    {
        return array(
            'object' => array(new \stdClass()),
            'int' => array(5),
            'float' => array(5.5),
            'string' => array('foo'),
            'boolean' => array(false),
            'resource' => array(tmpfile()),
        );
    }

    /**
     * @test
     *
     * @dataProvider provideDifferentPhpTypes
     */
    public function couldBeConstructedWithModelOfAnyType($phpType)
    {
        $this->getMockForAbstractClass('Payum\Core\Reply\BaseModelAware', array($phpType));
    }

    /**
     * @test
     *
     * @dataProvider provideDifferentPhpTypes
     */
    public function shouldAllowSetModelAndGetIt($phpType)
    {
        $request = $this->getMockForAbstractClass('Payum\Core\Reply\BaseModelAware', array(123321));

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
        $request = $this->getMockForAbstractClass('Payum\Core\Reply\BaseModelAware', array($phpType));

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
        $this->assertEquals($model, (array) $request->getModel());
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
        $this->assertEquals($model, (array) $request->getModel());
    }
}
