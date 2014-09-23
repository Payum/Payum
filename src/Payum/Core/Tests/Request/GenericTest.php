<?php
namespace Payum\Core\Tests\Request;

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
            'resource' => array(tmpfile())
        );
    }
    
    /**
     * @test
     */
    public function shouldBeAbstractClass()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\Generic');
        
        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function shouldImplementModelAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\Generic');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Request\ModelAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementSecuredRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\Generic');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Request\SecuredInterface'));
    }

    /**
     * @test
     * 
     * @dataProvider provideDifferentPhpTypes
     */
    public function couldBeConstructedWithModelOfAnyType($phpType)
    {
        $this->getMockForAbstractClass('Payum\Core\Request\Generic', array($phpType));
    }

    /**
     * @test
     *
     * @dataProvider provideDifferentPhpTypes
     */
    public function shouldAllowSetModelAndGetIt($phpType)
    {
        $request = $this->getMockForAbstractClass('Payum\Core\Request\Generic', array(123321));

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
        $request = $this->getMockForAbstractClass('Payum\Core\Request\Generic', array($phpType));
        
        $this->assertEquals($phpType, $request->getModel());
    }

    /**
     * @test
     */
    public function shouldAllowGetTokenSetInConstructor()
    {
        $tokenMock = $this->getMock('Payum\Core\Security\TokenInterface');

        $request = $this->getMockForAbstractClass('Payum\Core\Request\Generic', array($tokenMock));

        $this->assertSame($tokenMock, $request->getModel());
        $this->assertSame($tokenMock, $request->getToken());
    }

    /**
     * @test
     */
    public function shouldConvertArrayToArrayObjectInConstructor()
    {
        $model = array('foo' => 'bar');
        
        $request = $this->getMockForAbstractClass('Payum\Core\Request\Generic', array($model));

        $this->assertInstanceOf('ArrayObject', $request->getModel());
        $this->assertEquals($model, (array) $request->getModel());
    }

    /**
     * @test
     */
    public function shouldConvertArrayToArrayObjectSetWithSetter()
    {
        $request = $this->getMockForAbstractClass('Payum\Core\Request\Generic', array(123321));

        $model = array('foo' => 'bar');
        
        $request->setModel($model);

        $this->assertInstanceOf('ArrayObject', $request->getModel());
        $this->assertEquals($model, (array) $request->getModel());
    }
}