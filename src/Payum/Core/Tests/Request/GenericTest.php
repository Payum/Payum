<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;

class GenericTest extends TestCase
{
    public static function provideDifferentPhpTypes(): \Iterator
    {
        yield 'object' => array(new \stdClass());
        yield 'int' => array(5);
        yield 'float' => array(5.5);
        yield 'string' => array('foo');
        yield 'boolean' => array(false);
        yield 'resource' => array(tmpfile());
    }

    public function testShouldBeAbstractClass()
    {
        $rc = new \ReflectionClass(Generic::class);

        $this->assertTrue($rc->isAbstract());
    }

    public function testShouldImplementModelAwareInterface()
    {
        $rc = new \ReflectionClass(Generic::class);

        $this->assertTrue($rc->implementsInterface('Payum\Core\Model\ModelAwareInterface'));
    }

    public function testShouldImplementModelAggregateInterface()
    {
        $rc = new \ReflectionClass(Generic::class);

        $this->assertTrue($rc->implementsInterface('Payum\Core\Model\ModelAggregateInterface'));
    }

    public function testShouldImplementTokenAggregateInterface()
    {
        $rc = new \ReflectionClass(Generic::class);

        $this->assertTrue($rc->implementsInterface('Payum\Core\Security\TokenAggregateInterface'));
    }

    /**
     * @dataProvider provideDifferentPhpTypes
     */
    public function testCouldBeConstructedWithModelOfAnyType($phpType)
    {
        $request = new class($phpType) extends Generic {};

        $this->assertEquals($phpType, $request->getModel());
    }


    /**
     * @dataProvider provideDifferentPhpTypes
     */
    public function testShouldAllowSetModelAndGetIt($phpType)
    {
        $request = new class(123321) extends Generic {};

        $request->setModel($phpType);

        $this->assertEquals($phpType, $request->getModel());
    }

    /**
     * @dataProvider provideDifferentPhpTypes
     */
    public function testShouldAllowGetModelSetInConstructor($phpType)
    {
        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array($phpType));

        $this->assertEquals($phpType, $request->getModel());
    }

    public function testShouldAllowGetTokenSetInConstructor()
    {
        $tokenMock = $this->createMock('Payum\Core\Security\TokenInterface');

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array($tokenMock));

        $this->assertSame($tokenMock, $request->getModel());
        $this->assertSame($tokenMock, $request->getToken());
    }

    public function testShouldConvertArrayToArrayObjectInConstructor()
    {
        $model = array('foo' => 'bar');

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array($model));

        $this->assertInstanceOf('ArrayObject', $request->getModel());
        $this->assertSame($model, (array) $request->getModel());
    }

    public function testShouldConvertArrayToArrayObjectSetWithSetter()
    {
        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array(123321));

        $model = array('foo' => 'bar');

        $request->setModel($model);

        $this->assertInstanceOf('ArrayObject', $request->getModel());
        $this->assertSame($model, (array) $request->getModel());
    }

    public function testShouldNotSetTokenAsFirstModelOnConstruct()
    {
        /** @var Generic $request */
        $token = $this->createMock('Payum\Core\Security\TokenInterface');

        $request = $this->getMockForAbstractClass(Generic::class, array($token));

        $this->assertNull($request->getFirstModel());
    }

    public function testShouldNotSetIdentityAsFirstModelOnConstruct()
    {
        /** @var Generic $request */
        $identity = $this->createMock('Payum\Core\Storage\IdentityInterface', array(), array(), '', false);

        $request = $this->getMockForAbstractClass(Generic::class, array($identity));

        $this->assertNull($request->getFirstModel());
    }

    public function testShouldSetAnyObjectAsFirstModelOnConstruct()
    {
        $model = new \stdClass();

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array($model));

        $this->assertSame($model, $request->getFirstModel());
    }

    public function testShouldNotSetTokenAsFirstModelOnSetModel()
    {
        $token = $this->createMock('Payum\Core\Security\TokenInterface');

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array(null));
        $request->setModel($token);

        $this->assertNull($request->getFirstModel());
    }

    public function testShouldNotSetIdentityAsFirstModelOnSetModel()
    {
        $identity = $this->createMock('Payum\Core\Storage\IdentityInterface', array(), array(), '', false);

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array(null));
        $request->setModel($identity);

        $this->assertNull($request->getFirstModel());
    }

    public function testShouldSetAnyObjectAsFirstModelOnSetModel()
    {
        $model = new \stdClass();

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array(null));
        $request->setModel($model);

        $this->assertSame($model, $request->getFirstModel());
    }

    public function testShouldNotChangeFirstModelOnSecondSetModelCall()
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
