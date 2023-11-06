<?php
namespace Payum\Core\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\GetTokenAction;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetToken;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Tests\GenericActionTest;

class GetTokenActionTest extends GenericActionTest
{
    protected $requestClass = GetToken::class;

    protected $actionClass = GetTokenAction::class;

    /**
     * @var ActionInterface
     */
    protected $action;

    protected function setUp(): void
    {
        $this->action = new $this->actionClass($this->createMock(StorageInterface::class));
    }

    public function provideSupportedRequests(): \Iterator
    {
        yield array(new $this->requestClass('aHash'));
    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield array('foo');
        yield array(array('foo'));
        yield array(new \stdClass());
        yield array($this->getMockForAbstractClass(Generic::class, array(array())));
    }

    public function testShouldSetFoundToken()
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

    public function testThrowIfTokenNotFound()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
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
