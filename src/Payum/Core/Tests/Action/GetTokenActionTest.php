<?php

namespace Payum\Core\Tests\Action;

use Iterator;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\GetTokenAction;
use Payum\Core\Exception\LogicException;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetToken;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Tests\GenericActionTest;
use stdClass;

class GetTokenActionTest extends GenericActionTest
{
    /**
     * @var class-string<GetToken>
     */
    protected $requestClass = GetToken::class;

    /**
     * @var class-string<GetTokenAction>
     */
    protected $actionClass = GetTokenAction::class;

    /**
     * @var ActionInterface
     */
    protected $action;

    protected function setUp(): void
    {
        $this->action = new $this->actionClass($this->createMock(StorageInterface::class));
    }

    /**
     * @return \Iterator<GetToken[]>
     */
    public function provideSupportedRequests(): Iterator
    {
        yield [new $this->requestClass('aHash')];
    }

    public function provideNotSupportedRequests(): Iterator
    {
        yield ['foo'];
        yield [['foo']];
        yield [new stdClass()];
        yield [$this->getMockForAbstractClass(Generic::class, [[]])];
    }

    public function testShouldSetFoundToken(): void
    {
        $hash = 'theHash';
        $token = $this->createMock(TokenInterface::class);

        $tokenStorage = $this->createMock(StorageInterface::class);
        $tokenStorage
            ->expects($this->once())
            ->method('find')
            ->with($hash)
            ->willReturn($token)
        ;

        $action = new GetTokenAction($tokenStorage);

        $request = new GetToken($hash);

        $action->execute($request);

        $this->assertSame($token, $request->getToken());
    }

    public function testThrowIfTokenNotFound(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The token theHash could not be found');
        $hash = 'theHash';

        $tokenStorage = $this->createMock(StorageInterface::class);
        $tokenStorage
            ->expects($this->once())
            ->method('find')
            ->with($hash)
            ->willReturn(null)
        ;

        $action = new GetTokenAction($tokenStorage);

        $request = new GetToken($hash);

        $action->execute($request);
    }
}
