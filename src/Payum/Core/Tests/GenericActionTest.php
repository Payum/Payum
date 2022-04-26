<?php
namespace Payum\Core\Tests;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

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
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute($request)
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $this->action->execute($request);
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }

    /**
     * @return MockObject|TokenInterface
     */
    protected function createTokenMock()
    {
        return $this->createMock(TokenInterface::class);
    }
}
