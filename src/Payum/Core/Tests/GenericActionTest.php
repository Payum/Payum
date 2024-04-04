<?php
namespace Payum\Core\Tests;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;

abstract class GenericActionTest extends TestCase
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

    protected function setUp(): void
    {
        $this->action = new $this->actionClass();
    }

    public function provideSupportedRequests(): \Iterator
    {
        yield array(new $this->requestClass(array()));
        yield array(new $this->requestClass(new \ArrayObject()));
    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield array('foo');
        yield array(array('foo'));
        yield array(new \stdClass());
        yield array(new $this->requestClass('foo'));
        yield array(new $this->requestClass(new \stdClass()));
        yield array($this->getMockForAbstractClass(Generic::class, array(array())));
    }

    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass($this->actionClass);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @dataProvider provideSupportedRequests
     */
    public function testShouldSupportRequest($request)
    {
        $this->assertTrue($this->action->supports($request));
    }

    /**
     * @dataProvider provideNotSupportedRequests
     */
    public function testShouldNotSupportRequest($request)
    {
        $this->assertFalse($this->action->supports($request));
    }

    /**
     * @dataProvider provideNotSupportedRequests
     */
    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute($request)
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $this->action->execute($request);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|TokenInterface
     */
    protected function createTokenMock()
    {
        return $this->createMock(TokenInterface::class);
    }
}
