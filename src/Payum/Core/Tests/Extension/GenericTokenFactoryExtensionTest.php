<?php
namespace Payum\Core\Tests\Extension;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Extension\GenericTokenFactoryExtension;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;

class GenericTokenFactoryExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementExtensionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Extension\GenericTokenFactoryExtension');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Extension\ExtensionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithGenericTokenFactoryAsArgument()
    {
        new GenericTokenFactoryExtension($this->createGenericTokenFactoryMock());
    }

    /**
     * @test
     */
    public function shouldSetTokenFactoryToActionImplementsGenericTokenFactoryAwareInterface()
    {
        $tokenFactory = $this->createGenericTokenFactoryMock();

        $extension = new GenericTokenFactoryExtension($tokenFactory);

        $action = new ActionGenericTokenFactoryAware();

        $extension->onExecute(new \stdClass(), $action);

        $this->assertSame($tokenFactory, $action->tokenFactory);
    }

    /**
     * @test
     */
    public function shouldDoNothingIfActionNotImplementsGenericTokenFactoryAwareInterface()
    {
        $tokenFactory = $this->createGenericTokenFactoryMock();

        $extension = new GenericTokenFactoryExtension($tokenFactory);

        $action = $this->createActionMock();

        $extension->onExecute(new \stdClass(), $action);
    }

    /**
     * @test
     */
    public function shouldDoNothingOnPreExecute()
    {
        $tokenFactory = $this->createGenericTokenFactoryMock();

        $extension = new GenericTokenFactoryExtension($tokenFactory);

        $extension->onPreExecute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldDoNothingOnReply()
    {
        $request = new \stdClass();

        $tokenFactory = $this->createGenericTokenFactoryMock();

        $extension = new GenericTokenFactoryExtension($tokenFactory);

        $extension->onReply($this->createReplyMock(), $request, $this->createActionMock());
        $extension->onReply($this->createReplyMock(), $request, $action = new ActionGenericTokenFactoryAware());

        $this->assertNull($action->tokenFactory);
    }

    /**
     * @test
     */
    public function shouldDoNothingOnException()
    {
        $request = new \stdClass();

        $tokenFactory = $this->createGenericTokenFactoryMock();

        $extension = new GenericTokenFactoryExtension($tokenFactory);

        $extension->onException(new \Exception(), $request, $this->createActionMock());
        $extension->onException(new \Exception(), $request, $action = new ActionGenericTokenFactoryAware());

        $this->assertNull($action->tokenFactory);
    }

    /**
     * @test
     */
    public function shouldDoNothingOnPostExecute()
    {
        $request = new \stdClass();

        $tokenFactory = $this->createGenericTokenFactoryMock();

        $extension = new GenericTokenFactoryExtension($tokenFactory);

        $extension->onPostExecute($request, $this->createActionMock());
        $extension->onPostExecute($request, $action = new ActionGenericTokenFactoryAware());

        $this->assertNull($action->tokenFactory);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ReplyInterface
     */
    protected function createReplyMock()
    {
        return $this->getMock('Payum\Core\Reply\ReplyInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ActionInterface
     */
    protected function createActionMock()
    {
        return $this->getMock('Payum\Core\Action\ActionInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GenericTokenFactoryInterface
     */
    protected function createGenericTokenFactoryMock()
    {
        return $this->getMock('Payum\Core\Security\GenericTokenFactoryInterface');
    }
}

class ActionGenericTokenFactoryAware implements ActionInterface, GenericTokenFactoryAwareInterface
{
    public $tokenFactory;

    public function execute($request)
    {
    }

    public function supports($request)
    {
    }

    public function setGenericTokenFactory(GenericTokenFactoryInterface $genericTokenFactory = null)
    {
        $this->tokenFactory  = $genericTokenFactory;
    }
}
