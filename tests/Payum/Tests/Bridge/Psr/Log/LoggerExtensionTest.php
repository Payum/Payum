<?php
namespace Payum\Tests\Bridge\Psr\Log;

use Payum\Action\ActionInterface;
use Payum\Bridge\Psr\Log\LoggerExtension;
use Payum\Request\InteractiveRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class LoggerExtensionTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
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
        $rc = new \ReflectionClass('Payum\Bridge\Psr\Log\LoggerExtension');

        $this->assertTrue($rc->implementsInterface('Payum\Extension\ExtensionInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementLoggerAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Bridge\Psr\Log\LoggerExtension');

        $this->assertTrue($rc->implementsInterface('Psr\Log\LoggerAwareInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        $extension = new LoggerExtension;

        $this->assertAttributeInstanceOf('Psr\Log\NullLogger', 'logger', $extension);
    }

    /**
     * @test
     */
    public function couldBeConstructedWithCustomLoggergivenAsFirstArgument()
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

        $extension = new LoggerExtension;

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

        $action = new LoggerAwareAction;

        $extension->onExecute(new \stdClass, $action);

        $this->assertSame($logger, $action->logger);
    }

    /**
     * @test
     */
    public function shouldNotInjectLoggerToNotLoggerAwareActionOnExecute()
    {
        $logger = $this->createLoggerMock();

        $extension = new LoggerExtension($logger);

        $extension->onExecute(new \stdClass, $this->createActionMock());
    }

    /**
     * @test
     */
    public function shouldNotInjectLoggerToLoggerAwareActionOnPostExecute()
    {
        $logger = $this->createLoggerMock();

        $extension = new LoggerExtension($logger);

        $action = new LoggerAwareAction;

        $extension->onPostExecute(new \stdClass, $action);

        $this->assertNull($action->logger);
    }

    /**
     * @test
     */
    public function shouldNotInjectLoggerToLoggerAwareActionOnInteractiveRequest()
    {
        $logger = $this->createLoggerMock();

        $extension = new LoggerExtension($logger);

        $action = new LoggerAwareAction;

        $extension->onInteractiveRequest($this->createInteractiveRequestMock(), new \stdClass, $action);

        $this->assertNull($action->logger);
    }

    /**
     * @test
     */
    public function shouldNotInjectLoggerToLoggerAwareActionOnException()
    {
        $logger = $this->createLoggerMock();

        $extension = new LoggerExtension($logger);

        $action = new LoggerAwareAction;

        $extension->onException(new \Exception, new \stdClass, $action);

        $this->assertNull($action->logger);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    protected function createLoggerMock()
    {
        return $this->getMock('Psr\Log\LoggerInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|InteractiveRequestInterface
     */
    protected function createInteractiveRequestMock()
    {
        return $this->getMock('Payum\Request\InteractiveRequestInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ActionInterface
     */
    protected function createActionMock()
    {
        return $this->getMock('Payum\Action\ActionInterface');
    }
}

class LoggerAwareAction implements ActionInterface, LoggerAwareInterface
{
    public $logger;

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute($request) {}

    public function supports($request) {}
}