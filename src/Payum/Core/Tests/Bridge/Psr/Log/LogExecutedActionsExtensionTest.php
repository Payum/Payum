<?php

namespace Payum\Core\Tests\Bridge\Psr\Log;

use function get_class;
use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Psr\Log\LogExecutedActionsExtension;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Capture;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionObject;
use stdClass;

class LogExecutedActionsExtensionTest extends TestCase
{
    public function testShouldImplementExtensionInterface(): void
    {
        $rc = new ReflectionClass(LogExecutedActionsExtension::class);

        $this->assertTrue($rc->implementsInterface(ExtensionInterface::class));
    }

    public function testShouldImplementLoggerAwareInterface(): void
    {
        $rc = new ReflectionClass(LogExecutedActionsExtension::class);

        $this->assertTrue($rc->implementsInterface(LoggerAwareInterface::class));
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

        $context = new Context($this->createGatewayMock(), new stdClass(), []);

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

        $context = new Context($this->createGatewayMock(), new stdClass(), []);
        $context->setAction($this->createActionMock());

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPostExecute($context);
    }

    public function testShouldUsePreviousToGetPreviousRequestNumberOnExecute(): void
    {
        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('debug')
            ->with($this->stringStartsWith('[Payum] 2# '))
        ;

        $context = new Context($this->createGatewayMock(), new stdClass(), [
            new Context($this->createGatewayMock(), new stdClass(), []),
        ]);
        $context->setAction($this->createActionMock());

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onExecute($context);
    }

    public function testShouldLogNotObjectActionAndRequestOnExecute(): void
    {
        $stringRequest = 'a string';
        $arrayRequest = [];
        $action = new FooAction();

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->exactly(2))
            ->method('debug')
            ->withConsecutive(
                ['[Payum] 1# ' . get_class($action) . '::execute(string)'],
                ['[Payum] 1# ' . get_class($action) . '::execute(array)']
            )
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $context = new Context($this->createGatewayMock(), $stringRequest, []);
        $context->setAction($action);

        $extension->onExecute($context);

        $context = new Context($this->createGatewayMock(), $arrayRequest, []);
        $context->setAction($action);

        $extension->onExecute($context);
    }

    public function testShouldLogActionAndObjectRequestOnExecute(): void
    {
        $action = new FooAction();
        $stdRequest = new stdClass();
        $namespacedRequest = new NamespacedRequest();

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->exactly(2))
            ->method('debug')
            ->withConsecutive(
                ['[Payum] 1# ' . get_class($action) . '::execute(stdClass)'],
                ['[Payum] 1# ' . get_class($action) . '::execute(NamespacedRequest)']
            )
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $context = new Context($this->createGatewayMock(), $stdRequest, []);
        $context->setAction($action);

        $extension->onExecute($context);

        $context = new Context($this->createGatewayMock(), $namespacedRequest, []);
        $context->setAction($action);

        $extension->onExecute($context);
    }

    public function testShouldLogActionAndModelRequestWithModelNoObjectOnExecute(): void
    {
        $action = new FooAction();
        $model = [];
        $modelRequest = new Capture($model);

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('debug')
            ->with('[Payum] 1# ' . get_class($action) . '::execute(Capture{model: ArrayObject})')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $context = new Context($this->createGatewayMock(), $modelRequest, []);
        $context->setAction($action);

        $extension->onExecute($context);
    }

    public function testShouldLogActionAndModelRequestWithObjectModelOnExecute(): void
    {
        $action = new FooAction();
        $stdModel = new stdClass();
        $stdModelRequest = new Capture($stdModel);

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('debug')
            ->with('[Payum] 1# ' . get_class($action) . '::execute(Capture{model: stdClass})')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $context = new Context($this->createGatewayMock(), $stdModelRequest, []);
        $context->setAction($action);

        $extension->onExecute($context);
    }

    public function testShouldLogReplyWhenSetOnPostExecute(): void
    {
        $action = new FooAction();
        $replyMock = $this->createReplyMock();

        $ro = new ReflectionObject($replyMock);

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('debug')
            ->with('[Payum] 1# FooAction::execute(string) throws reply ' . $ro->getShortName())
        ;

        $context = new Context($this->createGatewayMock(), 'string', []);
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
            ->expects($this->once())
            ->method('debug')
            ->with('[Payum] 1# FooAction::execute(string) throws reply HttpRedirect{url: ' . $reply->getUrl() . '}')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $context = new Context($this->createGatewayMock(), 'string', []);
        $context->setAction($action);
        $context->setReply($reply);

        $extension->onPostExecute($context);
    }

    public function testShouldLogExceptionWhenSetOnPostExecute(): void
    {
        $action = new FooAction();

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('debug')
            ->with('[Payum] 1# FooAction::execute(string) throws exception LogicException')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $context = new Context($this->createGatewayMock(), 'string', []);
        $context->setAction($action);
        $context->setException(new LogicException());

        $extension->onPostExecute($context);
    }

    public function testShouldLogExceptionWhenSetButActionNotSetOnPostExecute(): void
    {
        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('debug')
            ->with('[Payum] 1# Gateway::execute(string) throws exception LogicException')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $context = new Context($this->createGatewayMock(), 'string', []);
        $context->setException(new LogicException());

        $extension->onPostExecute($context);
    }

    /**
     * @return MockObject|LoggerInterface
     */
    protected function createLoggerMock()
    {
        return $this->createMock(LoggerInterface::class);
    }

    /**
     * @return MockObject|ReplyInterface
     */
    protected function createReplyMock()
    {
        return $this->createMock(ReplyInterface::class);
    }

    /**
     * @return MockObject|ActionInterface
     */
    protected function createActionMock()
    {
        return $this->createMock(ActionInterface::class);
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}

class NamespacedRequest
{
}

class FooAction implements ActionInterface
{
    public function execute(mixed $request): void
    {
    }

    public function supports(mixed $request): bool
    {
        return true;
    }
}
