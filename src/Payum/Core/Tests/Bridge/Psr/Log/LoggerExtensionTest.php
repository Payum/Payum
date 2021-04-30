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

    /**
     * @test
     */
    public function shouldImplementExtensionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Psr\Log\LoggerExtension');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Extension\ExtensionInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementLoggerAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Psr\Log\LoggerExtension');

        $this->assertTrue($rc->implementsInterface('Psr\Log\LoggerAwareInterface'));
    }

    public function testItCouldBeConstructedWithoutAnyArguments()
    {
        $extension = new LoggerExtension();

        $this->assertAttributeInstanceOf('Psr\Log\NullLogger', 'logger', $extension);
    }

    public function testCouldBeConstructedWithCustomLoggerGivenAsFirstArgument()
    {
        $expectedLogger = $this->createLoggerMock();

        $extension = new LoggerExtension($expectedLogger);

        $this->assertAttributeSame($expectedLogger, 'logger', $extension);
    }

    /**
     * @test
     */
    public function shouldAllowSetLogger()
    {
        $expectedLogger = $this->createLoggerMock();

        $extension = new LoggerExtension();

        //guard
        $this->assertAttributeInstanceOf('Psr\Log\NullLogger', 'logger', $extension);

        $extension->setLogger($expectedLogger);

        $this->assertAttributeSame($expectedLogger, 'logger', $extension);
    }

    /**
     * @test
     */
    public function shouldInjectLoggerToLoggerAwareActionOnExecute()
    {
        $logger = $this->createLoggerMock();

        $extension = new LoggerExtension($logger);

        $action = new LoggerAwareAction();

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());
        $context->setAction($action);

        $extension->onExecute($context);

        $this->assertSame($logger, $action->logger);
    }

    /**
     * @test
     */
    public function shouldNotInjectLoggerToNotLoggerAwareActionOnExecute()
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

    /**
     * @test
     */
    public function shouldInjectNullLoggerToLoggerAwareActionOnPostExecute()
    {
        $logger = $this->createLoggerMock();

        $extension = new LoggerExtension($logger);

        $action = new LoggerAwareAction();

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());
        $context->setAction($action);

        $extension->onPostExecute($context);

        $this->assertInstanceOf('Psr\Log\NullLogger', $action->logger);
    }

    /**
     * @test
     */
    public function shouldNotInjectNullLoggerToNotLoggerAwareActionOnPostExecute()
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

    /**
     * @test
     */
    public function shouldDoNothingOnPreExecute()
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

    public function setLogger(LoggerInterface $logger)
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
