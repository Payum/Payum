<?php
namespace Payum\Tests\Bridge\Psr\Log;

use Payum\Action\ActionInterface;
use Payum\Bridge\Psr\Log\LogExecutedActionsExtension;
use Payum\Request\CaptureRequest;
use Payum\Request\CaptureTokenizedDetailsRequest;
use Payum\Request\InteractiveRequestInterface;
use Psr\Log\LoggerInterface;

class LogExecutedActionsExtensionTest extends \PHPUnit_Framework_TestCase
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
        $rc = new \ReflectionClass('Payum\Bridge\Psr\Log\LogExecutedActionsExtension');

        $this->assertTrue($rc->implementsInterface('Payum\Extension\ExtensionInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementLoggerAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Bridge\Psr\Log\LogExecutedActionsExtension');

        $this->assertTrue($rc->implementsInterface('Psr\Log\LoggerAwareInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        $extension = new LogExecutedActionsExtension;

        $this->assertAttributeInstanceOf('Psr\Log\NullLogger', 'logger', $extension);
    }

    /**
     * @test
     */
    public function couldBeConstructedWithCustomLoggergivenAsFirstArgument()
    {
        $expectedLogger = $this->createLoggerMock();

        $extension = new LogExecutedActionsExtension($expectedLogger);

        $this->assertAttributeSame($expectedLogger, 'logger', $extension);
    }

    /**
     * @test
     */
    public function shouldAllowSetLogger()
    {
        $expectedLogger = $this->createLoggerMock();

        $extension = new LogExecutedActionsExtension;

        //guard
        $this->assertAttributeInstanceOf('Psr\Log\NullLogger', 'logger', $extension);

        $extension->setLogger($expectedLogger);

        $this->assertAttributeSame($expectedLogger, 'logger', $extension);
    }

    /**
     * @test
     */
    public function shouldLogNotObjectActionAndRequestOnExecute()
    {
        $action = new FooAction;

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum][0] FooAction@'.spl_object_hash($action).'::execute(string)')
        ;
        $logger
            ->expects($this->at(1))
            ->method('debug')
            ->with('[Payum][0] FooAction@'.spl_object_hash($action).'::execute(array)')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onExecute('string', $action);
        $extension->onExecute(array(), $action);
    }

    /**
     * @test
     */
    public function shouldLogWithIncrementedStacklLevelOnExecute()
    {
        $action = new FooAction;

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum][2] FooAction@'.spl_object_hash($action).'::execute(string)')
        ;
        $logger
            ->expects($this->at(1))
            ->method('debug')
            ->with('[Payum][4] FooAction@'.spl_object_hash($action).'::execute(string)')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPreExecute('string');
        $extension->onPreExecute('string');
        $extension->onExecute('string', $action);

        $extension->onPreExecute('string');
        $extension->onPreExecute('string');
        $extension->onExecute('string', $action);
    }

    /**
     * @test
     */
    public function shouldLogActionAndObjectRequestOnExecute()
    {
        $action = new FooAction;
        $stdRequest = new \stdClass;
        $namespacedRequest = new NamespacedRequest;

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum][0] FooActio1n@'.spl_object_hash($action).'::execute(stdClass@'.spl_object_hash($stdRequest).')')
        ;
        $logger
            ->expects($this->at(1))
            ->method('debug')
            ->with('[Payum][0] FooAction@'.spl_object_hash($action).'::execute(NamespacedRequest@'.spl_object_hash($namespacedRequest).')')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onExecute($stdRequest, $action);
        $extension->onExecute($namespacedRequest, $action);
    }

    /**
     * @test
     */
    public function shouldLogActionAndModelRequestWithModelNoObjectOnExecute()
    {
        $action = new FooAction;
        $model = array();
        $modelRequest = new CaptureRequest($model);

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with($this->stringStartsWith('[Payum][0] FooAction@'.spl_object_hash($action).'::execute(CaptureRequest@'.spl_object_hash($modelRequest).'{ArrayObject@'))
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onExecute($modelRequest, $action);
    }

    /**
     * @test
     */
    public function shouldLogActionAndModelRequestWithObjectModelOnExecute()
    {
        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum][0][PreExecute] CaptureRequest{stdClass}')
        ;

        $extension = new LogExecutedActionsExtension($logger);


        $capture = new CaptureRequest(new \stdClass);

        //guard
        $this->assertInstanceOf('Payum\Request\ModelRequestInterface', $capture);

        $extension->onPreExecute($capture);
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