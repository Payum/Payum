<?php

namespace Payum\Core\Tests\Bridge\Psr\Log;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Psr\Log\LoggerExtension;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use stdClass;

class LoggerExtensionTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (false == interface_exists(LoggerInterface::class)) {
            throw new SkippedTestError('To run these tests install psr log lib.');
        }
    }

    public function testShouldImplementExtensionInterface(): void
    {
        $rc = new ReflectionClass(LoggerExtension::class);

        $this->assertTrue($rc->implementsInterface(ExtensionInterface::class));
    }

    public function testShouldImplementLoggerAwareInterface(): void
    {
        $rc = new ReflectionClass(LoggerExtension::class);

        $this->assertTrue($rc->implementsInterface(LoggerAwareInterface::class));
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

        $context = new Context($this->createGatewayMock(), new stdClass(), []);
        $context->setAction($action);

        $extension->onExecute($context);

        $this->assertSame($logger, $action->logger);
    }

    public function testShouldNotInjectLoggerToNotLoggerAwareActionOnExecute(): void
    {
        $logger = $this->createLoggerMock();

        $extension = new LoggerExtension($logger);

        $context = new Context($this->createGatewayMock(), new stdClass(), []);
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

        $context = new Context($this->createGatewayMock(), new stdClass(), []);
        $context->setAction($action);

        $extension->onPostExecute($context);

        $this->assertInstanceOf(NullLogger::class, $action->logger);
    }

    public function testShouldNotInjectNullLoggerToNotLoggerAwareActionOnPostExecute(): void
    {
        $logger = $this->createLoggerMock();

        $extension = new LoggerExtension($logger);

        $context = new Context($this->createGatewayMock(), new stdClass(), []);

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

        $context = new Context($this->createGatewayMock(), new stdClass(), []);
        $context->setAction($action);

        $extension->onPreExecute($context);

        $this->assertNull($action->logger);
    }

    /**
     * @return MockObject|LoggerInterface
     */
    protected function createLoggerMock()
    {
        return $this->createMock(LoggerInterface::class);
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

class LoggerAwareAction implements ActionInterface, LoggerAwareInterface
{
    public $logger;

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function execute($request): void
    {
    }

    public function supports($request): void
    {
    }
}
