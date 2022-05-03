<?php
namespace Payum\Core\Tests\Bridge\Psr\Log;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Psr\Log\LoggerExtension;
use Payum\Core\Extension\Context;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use PHPUnit\Framework\SkippedTestError;

class LoggerExtensionTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (false == interface_exists('Psr\Log\LoggerInterface')) {
            throw new SkippedTestError('To run these tests install psr log lib.');
        }
    }

    public function testShouldImplementExtensionInterface(): void
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Psr\Log\LoggerExtension');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Extension\ExtensionInterface'));
    }

    public function testShouldImplementLoggerAwareInterface(): void
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Psr\Log\LoggerExtension');

        $this->assertTrue($rc->implementsInterface('Psr\Log\LoggerAwareInterface'));
    }

    public function testShouldAllowSetLogger(): void
    {
        $extension = new LoggerExtension();
        $this->assertInstanceOf(LoggerAwareInterface::class, $extension);
    }

    public function testShouldInjectLoggerToLoggerAwareActionOnExecute(): void
    {
        $logger = $this->createLoggerMock();

        $extension = new LoggerExtension($logger);

        $action = new LoggerAwareAction();

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());
        $context->setAction($action);

        $extension->onExecute($context);

        $this->assertSame($logger, $action->logger);
    }

    public function testShouldNotInjectLoggerToNotLoggerAwareActionOnExecute(): void
    {
        $logger = $this->createLoggerMock();

        $extension = new LoggerExtension($logger);

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());
        $action = $this->createMock(LoggerAwareAction::class);
        $action->expects($this->once())
            ->method('setLogger')
            ->with($logger);

        $context->setAction($action);

        $extension->onExecute($context);
    }

    public function testShouldInjectNullLoggerToLoggerAwareActionOnPostExecute(): void
    {
        $logger = $this->createLoggerMock();

        $extension = new LoggerExtension($logger);

        $action = new LoggerAwareAction();

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());
        $context->setAction($action);

        $extension->onPostExecute($context);

        $this->assertInstanceOf('Psr\Log\NullLogger', $action->logger);
    }

    public function testShouldNotInjectNullLoggerToNotLoggerAwareActionOnPostExecute(): void
    {
        $logger = $this->createLoggerMock();

        $extension = new LoggerExtension($logger);

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());

        $action = $this->createMock(LoggerAwareAction::class);
        $action->expects($this->once())
            ->method('setLogger')
            ->with(new NullLogger());

        $context->setAction($action);

        $extension->onPostExecute($context);
    }

    public function testShouldDoNothingOnPreExecute(): void
    {
        $logger = $this->createLoggerMock();

        $extension = new LoggerExtension($logger);

        $action = new LoggerAwareAction();

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());
        $context->setAction($action);

        $extension->onPreExecute($context);

        $this->assertNull($action->logger);
    }

    protected function createLoggerMock(): MockObject|LoggerInterface
    {
        return $this->createMock('Psr\Log\LoggerInterface');
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

class LoggerAwareAction implements ActionInterface, LoggerAwareInterface
{
    public $logger;

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function execute($request)
    {
    }

    public function supports($request)
    {
    }
}
