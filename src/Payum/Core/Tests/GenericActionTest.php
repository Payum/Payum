<?php

namespace Payum\Core\Tests;

use ArrayObject;
use Iterator;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

abstract class GenericActionTest extends TestCase
{
    /**
     * @var class-string
     */
    protected $requestClass;

    /**
     * @var class-string
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

    public function provideSupportedRequests(): Iterator
    {
        yield [new $this->requestClass([])];
        yield [new $this->requestClass(new ArrayObject())];
    }

    public function provideNotSupportedRequests(): Iterator
    {
        yield ['foo'];
        yield [['foo']];
        yield [new stdClass()];
        yield [new $this->requestClass('foo')];
        yield [new $this->requestClass(new stdClass())];
        yield [$this->getMockForAbstractClass(Generic::class, [[]])];
    }

    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass($this->actionClass);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @dataProvider provideSupportedRequests
     */
    public function testShouldSupportRequest(object $request): void
    {
        $this->assertTrue($this->action->supports($request));
    }

    /**
     * @dataProvider provideNotSupportedRequests
     */
    public function testShouldNotSupportRequest($request): void
    {
        $this->assertFalse($this->action->supports($request));
    }

    /**
     * @dataProvider provideNotSupportedRequests
     */
    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute($request): void
    {
        $this->expectException(RequestNotSupportedException::class);
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
