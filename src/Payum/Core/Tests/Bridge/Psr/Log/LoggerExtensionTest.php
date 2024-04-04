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

class LoggerExtensionTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (false == interface_exists('Psr\Log\LoggerInterface')) {
            throw new \PHPUnit_Framework_SkippedTestError('To run these tests install psr log lib.');
        }
    }

    public function testShouldImplementExtensionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Psr\Log\LoggerExtension');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Extension\ExtensionInterface'));
    }

    public function testShouldImplementLoggerAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Psr\Log\LoggerExtension');

        $this->assertTrue($rc->implementsInterface('Psr\Log\LoggerAwareInterface'));
    }

    public function testShouldAllowSetLogger()
    {
        $extension = new LoggerExtension();
        $this->assertInstanceOf(LoggerAwareInterface::class, $extension);
    }

    public function testShouldInjectLoggerToLoggerAwareActionOnExecute()
    {
        $logger = $this->createLoggerMock();

        $extension = new LoggerExtension($logger);

        $action = new LoggerAwareAction();

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());
        $context->setAction($action);

        $extension->onExecute($context);

        $this->assertSame($logger, $action->logger);
    }

    public function testShouldNotInjectLoggerToNotLoggerAwareActionOnExecute()
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

    public function testShouldInjectNullLoggerToLoggerAwareActionOnPostExecute()
    {
        $logger = $this->createLoggerMock();

        $extension = new LoggerExtension($logger);

        $action = new LoggerAwareAction();

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());
        $context->setAction($action);

        $extension->onPostExecute($context);

        $this->assertInstanceOf('Psr\Log\NullLogger', $action->logger);
    }

    public function testShouldNotInjectNullLoggerToNotLoggerAwareActionOnPostExecute()
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

    public function testShouldDoNothingOnPreExecute()
    {
        $logger = $this->createLoggerMock();

        $extension = new LoggerExtension($logger);

        $action = new LoggerAwareAction();

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());
        $context->setAction($action);

        $extension->onPreExecute($context);

        $this->assertNull($action->logger);
    }

    /**
     * @return MockObject|LoggerInterface
     */
    protected function createLoggerMock()
    {
        return $this->createMock('Psr\Log\LoggerInterface');
    }

    /**
     * @return MockObject|ActionInterface
     */
    protected function createActionMock()
    {
        return $this->createMock('Payum\Core\Action\ActionInterface');
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
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
