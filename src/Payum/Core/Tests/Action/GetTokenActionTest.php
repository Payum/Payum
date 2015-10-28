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

    protected function setUp()
    {
        $this->action = new $this->actionClass($this->getMock(StorageInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        //overwrite
    }

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass('aHash')),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array($this->getMockForAbstractClass(Generic::class, array(array()))),
        );
    }

    /**
     * @test
     */
    public function shouldSetFoundToken()
    {
        $hash = 'theHash';
        $token = $this->getMock(TokenInterface::class);

        $tokenStorage = $this->getMock(StorageInterface::class);
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

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The token theHash could not be found
     */
    public function throwIfTokenNotFound()
    {
        $hash = 'theHash';

        $tokenStorage = $this->getMock(StorageInterface::class);
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
