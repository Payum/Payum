<?php
namespace Payum\Core\Tests\Bridge\Psr\Log;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Psr\Log\LogExecutedActionsExtension;
use Payum\Core\Extension\Context;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Reply\HttpRedirect;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class LogExecutedActionsExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementExtensionInterface(): void
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Psr\Log\LogExecutedActionsExtension');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Extension\ExtensionInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementLoggerAwareInterface(): void
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Psr\Log\LogExecutedActionsExtension');

        $this->assertTrue($rc->implementsInterface('Psr\Log\LoggerAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldAllowSetLogger(): void
    {
        $extension = new LogExecutedActionsExtension();

        $this->assertInstanceOf(LoggerAwareInterface::class, $extension);
    }

    /**
     * @test
     */
    public function shouldNotLogAnythingOnPreExecute(): void
    {
        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->never())
            ->method('debug')
        ;

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPreExecute($context);
    }

    /**
     * @test
     */
    public function shouldNotLogAnythingOnPostExecute(): void
    {
        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->never())
            ->method('debug')
        ;

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());
        $context->setAction($this->createActionMock());

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPostExecute($context);
    }

    /**
     * @test
     */
    public function shouldUsePreviousToGetPreviousRequestNumberOnExecute(): void
    {
        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with($this->stringStartsWith('[Payum] 2# '))
        ;

        $context = new Context($this->createGatewayMock(), new \stdClass(), array(
            new Context($this->createGatewayMock(), new \stdClass(), array()),
        ));
        $context->setAction($this->createActionMock());

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onExecute($context);
    }

    /**
     * @test
     */
    public function shouldLogNotObjectActionAndRequestOnExecute(): void
    {
        $stringRequest = 'a string';
        $arrayRequest = array();
        $action = new FooAction();

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum] 1# '.get_class($action).'::execute(string)')
        ;
        $logger
            ->expects($this->at(1))
            ->method('debug')
            ->with('[Payum] 1# '.get_class($action).'::execute(array)')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $context = new Context($this->createGatewayMock(), $stringRequest, array());
        $context->setAction($action);

        $extension->onExecute($context);

        $context = new Context($this->createGatewayMock(), $arrayRequest, array());
        $context->setAction($action);

        $extension->onExecute($context);
    }

    /**
     * @test
     */
    public function shouldLogActionAndObjectRequestOnExecute(): void
    {
        $action = new FooAction();
        $stdRequest = new \stdClass();
        $namespacedRequest = new NamespacedRequest();

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum] 1# '.get_class($action).'::execute(stdClass)')
        ;
        $logger
            ->expects($this->at(1))
            ->method('debug')
            ->with('[Payum] 1# '.get_class($action).'::execute(NamespacedRequest)')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $context = new Context($this->createGatewayMock(), $stdRequest, array());
        $context->setAction($action);

        $extension->onExecute($context);

        $context = new Context($this->createGatewayMock(), $namespacedRequest, array());
        $context->setAction($action);

        $extension->onExecute($context);
    }

    /**
     * @test
     */
    public function shouldLogActionAndModelRequestWithModelNoObjectOnExecute(): void
    {
        $action = new FooAction();
        $model = array();
        $modelRequest = new Capture($model);

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum] 1# '.get_class($action).'::execute(Capture{model: ArrayObject})')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $context = new Context($this->createGatewayMock(), $modelRequest, array());
        $context->setAction($action);

        $extension->onExecute($context);
    }

    /**
     * @test
     */
    public function shouldLogActionAndModelRequestWithObjectModelOnExecute(): void
    {
        $action = new FooAction();
        $stdModel = new \stdClass();
        $stdModelRequest = new Capture($stdModel);

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum] 1# '.get_class($action).'::execute(Capture{model: stdClass})')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $context = new Context($this->createGatewayMock(), $stdModelRequest, array());
        $context->setAction($action);

        $extension->onExecute($context);
    }

    /**
     * @test
     */
    public function shouldLogReplyWhenSetOnPostExecute(): void
    {
        $action = new FooAction();
        $replyMock = $this->createReplyMock();

        $ro = new \ReflectionObject($replyMock);

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum] 1# FooAction::execute(string) throws reply '.$ro->getShortName())
        ;

        $context = new Context($this->createGatewayMock(), 'string', array());
        $context->setAction($action);
        $context->setReply($replyMock);

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPostExecute($context);
    }

    /**
     * @test
     */
    public function shouldLogHttpRedirectReplyWithUrlIncludedOnPostExecute(): void
    {
        $action = new FooAction();
        $reply = new HttpRedirect('http://example.com');

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum] 1# FooAction::execute(string) throws reply HttpRedirect{url: '.$reply->getUrl().'}')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $context = new Context($this->createGatewayMock(), 'string', array());
        $context->setAction($action);
        $context->setReply($reply);

        $extension->onPostExecute($context);
    }

    /**
     * @test
     */
    public function shouldLogExceptionWhenSetOnPostExecute(): void
    {
        $action = new FooAction();

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum] 1# FooAction::execute(string) throws exception LogicException')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $context = new Context($this->createGatewayMock(), 'string', array());
        $context->setAction($action);
        $context->setException(new \LogicException());

        $extension->onPostExecute($context);
    }

    /**
     * @test
     */
    public function shouldLogExceptionWhenSetButActionNotSetOnPostExecute(): void
    {
        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum] 1# Gateway::execute(string) throws exception LogicException')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $context = new Context($this->createGatewayMock(), 'string', array());
        $context->setException(new \LogicException());

        $extension->onPostExecute($context);
    }

    protected function createLoggerMock(): MockObject|LoggerInterface
    {
        return $this->createMock('Psr\Log\LoggerInterface');
    }

    protected function createReplyMock(): MockObject|ReplyInterface
    {
        return $this->createMock('Payum\Core\Reply\ReplyInterface');
    }

    protected function createActionMock(): MockObject|ActionInterface
    {
        return $this->createMock('Payum\Core\Action\ActionInterface');
    }

    protected function createGatewayMock(): GatewayInterface|MockObject
    {
        return $this->createMock('Payum\Core\GatewayInterface');
    }
}

class NamespacedRequest
{
}

class FooAction implements ActionInterface
{
    public function execute($request)
    {
    }

    public function supports($request)
    {
    }
}
