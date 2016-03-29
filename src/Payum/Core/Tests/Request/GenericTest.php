<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Generic;

class GenericTest extends \PHPUnit_Framework_TestCase
{
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
     */
    public function shouldBeAbstractClass()
    {
        $rc = new \ReflectionClass(Generic::class);

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function shouldImplementModelAwareInterface()
    {
        $rc = new \ReflectionClass(Generic::class);

        $this->assertTrue($rc->implementsInterface('Payum\Core\Model\ModelAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementModelAggregateInterface()
    {
        $rc = new \ReflectionClass(Generic::class);

        $this->assertTrue($rc->implementsInterface('Payum\Core\Model\ModelAggregateInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementTokenAggregateInterface()
    {
        $rc = new \ReflectionClass(Generic::class);

        $this->assertTrue($rc->implementsInterface('Payum\Core\Security\TokenAggregateInterface'));
    }

    /**
     * @test
     *
     * @dataProvider provideDifferentPhpTypes
     */
    public function couldBeConstructedWithModelOfAnyType($phpType)
    {
        $this->getMockForAbstractClass(Generic::class, array($phpType));
    }

    /**
     * @test
     *
     * @dataProvider provideDifferentPhpTypes
     */
    public function shouldAllowSetModelAndGetIt($phpType)
    {
        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array(123321));

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
        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array($phpType));

        $this->assertEquals($phpType, $request->getModel());
    }

    /**
     * @test
     */
    public function shouldAllowGetTokenSetInConstructor()
    {
        $tokenMock = $this->getMock('Payum\Core\Security\TokenInterface');

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array($tokenMock));

        $this->assertSame($tokenMock, $request->getModel());
        $this->assertSame($tokenMock, $request->getToken());
    }

    /**
     * @test
     */
    public function shouldConvertArrayToArrayObjectInConstructor()
    {
        $model = array('foo' => 'bar');

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array($model));

        $this->assertInstanceOf('ArrayObject', $request->getModel());
        $this->assertEquals($model, (array) $request->getModel());
    }

    /**
     * @test
     */
    public function shouldConvertArrayToArrayObjectSetWithSetter()
    {
        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array(123321));

        $model = array('foo' => 'bar');

        $request->setModel($model);

        $this->assertInstanceOf('ArrayObject', $request->getModel());
        $this->assertEquals($model, (array) $request->getModel());
    }

    /**
     * @test
     */
    public function shouldNotSetTokenAsFirstModelOnConstruct()
    {
        /** @var Generic $request */
        $token = $this->getMock('Payum\Core\Security\TokenInterface');

        $request = $this->getMockForAbstractClass(Generic::class, array($token));

        $this->assertNull($request->getFirstModel());
    }

    /**
     * @test
     */
    public function shouldNotSetIdentityAsFirstModelOnConstruct()
    {
        /** @var Generic $request */
        $identity = $this->getMock('Payum\Core\Storage\IdentityInterface', array(), array(), '', false);

        $request = $this->getMockForAbstractClass(Generic::class, array($identity));

        $this->assertNull($request->getFirstModel());
    }

    /**
     * @test
     */
    public function shouldSetAnyObjectAsFirstModelOnConstruct()
    {
        $model = new \stdClass();

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array($model));

        $this->assertSame($model, $request->getFirstModel());
    }

    /**
     * @test
     */
    public function shouldNotSetTokenAsFirstModelOnSetModel()
    {
        $token = $this->getMock('Payum\Core\Security\TokenInterface');

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array(null));
        $request->setModel($token);

        $this->assertNull($request->getFirstModel());
    }

    /**
     * @test
     */
    public function shouldNotSetIdentityAsFirstModelOnSetModel()
    {
        $identity = $this->getMock('Payum\Core\Storage\IdentityInterface', array(), array(), '', false);

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array(null));
        $request->setModel($identity);

        $this->assertNull($request->getFirstModel());
    }

    /**
     * @test
     */
    public function shouldSetAnyObjectAsFirstModelOnSetModel()
    {
        $model = new \stdClass();

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array(null));
        $request->setModel($model);

        $this->assertSame($model, $request->getFirstModel());
    }

    /**
     * @test
     */
    public function shouldNotChangeFirstModelOnSecondSetModelCall()
    {
        $firstModel = new \stdClass();
        $secondModel = new \stdClass();

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array($firstModel));
        $request->setModel($secondModel);

        $this->assertSame($firstModel, $request->getFirstModel());
        $this->assertSame($secondModel, $request->getModel());
    }
}
