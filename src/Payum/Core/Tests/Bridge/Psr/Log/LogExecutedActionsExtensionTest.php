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
    public function testShouldImplementExtensionInterface(): void
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Psr\Log\LogExecutedActionsExtension');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Extension\ExtensionInterface'));
    }

    public function testShouldImplementLoggerAwareInterface(): void
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Psr\Log\LogExecutedActionsExtension');

        $this->assertTrue($rc->implementsInterface('Psr\Log\LoggerAwareInterface'));
    }

    public function testShouldAllowSetLogger(): void
    {
        $extension = new LogExecutedActionsExtension();

        $this->assertInstanceOf(LoggerAwareInterface::class, $extension);
    }

    public function testShouldNotLogAnythingOnPreExecute(): void
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

    public function testShouldNotLogAnythingOnPostExecute(): void
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

    public function testShouldUsePreviousToGetPreviousRequestNumberOnExecute(): void
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

    public function testShouldLogNotObjectActionAndRequestOnExecute(): void
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

    public function testShouldLogActionAndObjectRequestOnExecute(): void
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

    public function testShouldLogActionAndModelRequestWithModelNoObjectOnExecute(): void
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

    public function testShouldLogActionAndModelRequestWithObjectModelOnExecute(): void
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

    public function testShouldLogReplyWhenSetOnPostExecute(): void
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

    public function testShouldLogHttpRedirectReplyWithUrlIncludedOnPostExecute(): void
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

    public function testShouldLogExceptionWhenSetOnPostExecute(): void
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

    public function testShouldLogExceptionWhenSetButActionNotSetOnPostExecute(): void
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
