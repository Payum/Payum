<?php
namespace Payum\Tests\Bridge\Psr\Log;

use Payum\Action\ActionInterface;
use Payum\Bridge\Psr\Log\LogExecutedActionsExtension;
use Payum\Request\CaptureRequest;
use Payum\Request\CaptureTokenizedDetailsRequest;
use Payum\Request\InteractiveRequestInterface;
use Payum\Request\RedirectUrlInteractiveRequest;
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
    public function shouldNotLogAnythingOnPreExecute()
    {
        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->never())
            ->method('debug')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPreExecute(new \stdClass);
    }

    /**
     * @test
     */
    public function shouldNotLogAnythingOnExecute()
    {
        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->never())
            ->method('debug')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onExecute(new \stdClass, $this->createActionMock());
    }

    /**
     * @test
     */
    public function shouldIncrementStackOnPreExecute()
    {
        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with($this->stringStartsWith('[Payum] 2. '))
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPreExecute('string');
        $extension->onPreExecute('string');
        $extension->onPostExecute(new \stdClass, $this->createActionMock());
    }

    /**
     * @test
     */
    public function shouldDecrementStackOnPostExecute()
    {
        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with($this->stringStartsWith('[Payum] 2. '))
        ;
        $logger
            ->expects($this->at(1))
            ->method('debug')
            ->with($this->stringStartsWith('[Payum] 1. '))
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPreExecute('string');
        $extension->onPreExecute('string');
        $extension->onPostExecute(new \stdClass, $this->createActionMock());
        $extension->onPostExecute(new \stdClass, $this->createActionMock());
    }

    /**
     * @test
     */
    public function shouldDecrementStackOnException()
    {
        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with($this->stringStartsWith('[Payum] 2. '))
        ;
        $logger
            ->expects($this->at(1))
            ->method('debug')
            ->with($this->stringStartsWith('[Payum] 1. '))
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPreExecute('string');
        $extension->onPreExecute('string');
        $extension->onException(new \Exception, new \stdClass);
        $extension->onException(new \Exception, new \stdClass, $this->createActionMock());
    }

    /**
     * @test
     */
    public function shouldDecrementStackOnInteractiveRequest()
    {
        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with($this->stringStartsWith('[Payum] 2. '))
        ;
        $logger
            ->expects($this->at(1))
            ->method('debug')
            ->with($this->stringStartsWith('[Payum] 1. '))
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPreExecute('string');
        $extension->onPreExecute('string');
        $extension->onInteractiveRequest($this->createInteractiveRequestMock(), new \stdClass, $this->createActionMock());
        $extension->onInteractiveRequest($this->createInteractiveRequestMock(), new \stdClass, $this->createActionMock());
    }

    /**
     * @test
     */
    public function shouldLogNotObjectActionAndRequestOnPostExecute()
    {
        $action = new FooAction;

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum] 1. FooAction::execute(string)')
        ;
        $logger
            ->expects($this->at(1))
            ->method('debug')
            ->with('[Payum] 1. FooAction::execute(array)')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPreExecute('string');
        $extension->onPostExecute('string', $action);

        $extension->onPreExecute(array());
        $extension->onPostExecute(array(), $action);
    }

    /**
     * @test
     */
    public function shouldLogActionAndObjectRequestOnPostExecute()
    {
        $action = new FooAction;
        $stdRequest = new \stdClass;
        $namespacedRequest = new NamespacedRequest;

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum] 1. FooAction::execute(stdClass)')
        ;
        $logger
            ->expects($this->at(1))
            ->method('debug')
            ->with('[Payum] 1. FooAction::execute(NamespacedRequest)')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPreExecute($stdRequest);
        $extension->onPostExecute($stdRequest, $action);

        $extension->onPreExecute($namespacedRequest);
        $extension->onPostExecute($namespacedRequest, $action);
    }

    /**
     * @test
     */
    public function shouldLogActionAndModelRequestWithModelNoObjectOnPostExecute()
    {
        $action = new FooAction;
        $model = array();
        $modelRequest = new CaptureRequest($model);

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with($this->stringStartsWith('[Payum] 1. FooAction::execute(CaptureRequest{ArrayObject@'))
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPreExecute($modelRequest);
        $extension->onPostExecute($modelRequest, $action);
    }

    /**
     * @test
     */
    public function shouldLogActionAndModelRequestWithObjectModelOnPostExecute()
    {
        $action = new FooAction;
        $stdModel = new \stdClass;
        $stdModelRequest = new CaptureRequest($stdModel);

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum] 1. FooAction::execute(CaptureRequest{stdClass@'.spl_object_hash($stdModel).'})')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPreExecute($stdModelRequest);
        $extension->onPostExecute($stdModelRequest, $action);
    }

    /**
     * @test
     */
    public function shouldLogOnInteractiveRequest()
    {
        $action = new FooAction;
        $interactiveRequest = $this->createInteractiveRequestMock();

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum] 1. FooAction::execute(string) throws interactive '.get_class($interactiveRequest))
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPreExecute('string');
        $extension->onInteractiveRequest($interactiveRequest, 'string', $action);
    }

    /**
     * @test
     */
    public function shouldLogRedirectUrlInteractiveRequestWithUrlIncludedOnInteractiveRequest()
    {
        $action = new FooAction;
        $interactiveRequest = new RedirectUrlInteractiveRequest('http://example.com');

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum] 1. FooAction::execute(string) throws interactive RedirectUrlInteractiveRequest('.$interactiveRequest->getUrl().')')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPreExecute('string');
        $extension->onInteractiveRequest($interactiveRequest, 'string', $action);
    }

    /**
     * @test
     */
    public function shouldLogOnExceptionWhenActionPassed()
    {
        $action = new FooAction;

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum] 1. FooAction::execute(string) throws exception LogicException')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPreExecute('string');
        $extension->onException(new \LogicException, 'string', $action);
    }

    /**
     * @test
     */
    public function shouldLogOnExceptionWhenActionNotPassed()
    {
        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum] 1. Payment::execute(string) throws exception LogicException')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPreExecute('string');
        $extension->onException(new \LogicException, 'string');
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