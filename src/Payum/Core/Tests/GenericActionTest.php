<?php
namespace Payum\Core\Tests;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Security\TokenInterface;

abstract class GenericActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Generic
     */
    protected $requestClass;

    /**
     * @var string
     */
    protected $actionClass;

    /**
     * @var ActionInterface
     */
    protected $action;

    protected function setUp()
    {
        $this->action = new $this->actionClass();
    }

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass(array())),
            array(new $this->requestClass(new \ArrayObject())),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array(new $this->requestClass('foo')),
            array(new $this->requestClass(new \stdClass())),
            array($this->getMockForAbstractClass(Generic::class, array(array()))),
        );
    }

    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass($this->actionClass);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new $this->actionClass();
    }

    /**
     * @test
     *
     * @dataProvider provideSupportedRequests
     */
    public function shouldSupportRequest($request)
    {
        $this->assertTrue($this->action->supports($request));
    }

    /**
     * @test
     *
     * @dataProvider provideNotSupportedRequests
     */
    public function shouldNotSupportRequest($request)
    {
        $this->assertFalse($this->action->supports($request));
    }

    /**
     * @test
     *
     * @dataProvider provideNotSupportedRequests
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute($request)
    {
        $this->action->execute($request);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock(GatewayInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|TokenInterface
     */
    protected function createTokenMock()
    {
        return $this->getMock(TokenInterface::class);
    }
}
