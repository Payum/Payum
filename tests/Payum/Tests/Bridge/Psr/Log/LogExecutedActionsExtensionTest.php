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
    public function shouldNotLogAnythingOnPostExecute()
    {
        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->never())
            ->method('debug')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPostExecute(new \stdClass, $this->createActionMock());
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
            ->with($this->stringStartsWith('[Payum] 2# '))
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPreExecute('string');
        $extension->onPreExecute('string');
        $extension->onExecute(new \stdClass, $this->createActionMock());
    }

    /**
     * @test
     */
    public function shouldDecrementStackOnPostExecute()
    {
        $request = new \stdClass;
        $action = $this->createActionMock();

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with($this->stringStartsWith('[Payum] 2# '))
        ;
        $logger
            ->expects($this->at(1))
            ->method('debug')
            ->with($this->stringStartsWith('[Payum] 1# '))
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPreExecute($request);
        $extension->onPreExecute($request);

        $extension->onExecute($request, $action);

        $extension->onPostExecute($request, $action);

        $extension->onExecute($request, $action);
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
            ->with($this->stringStartsWith('[Payum] 2# '))
        ;
        $logger
            ->expects($this->at(1))
            ->method('debug')
            ->with($this->stringStartsWith('[Payum] 1# '))
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
            ->with($this->stringStartsWith('[Payum] 2# '))
        ;
        $logger
            ->expects($this->at(1))
            ->method('debug')
            ->with($this->stringStartsWith('[Payum] 1# '))
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
    public function shouldLogNotObjectActionAndRequestOnExecute()
    {
        $stringRequest = 'a string';
        $arrayRequest = array();
        $action = new FooAction;

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

        $extension->onPreExecute($stringRequest);

        $extension->onExecute($stringRequest, $action);
        $extension->onExecute($arrayRequest, $action);
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
            ->with('[Payum] 1# '.get_class($action).'::execute(stdClass)')
        ;
        $logger
            ->expects($this->at(1))
            ->method('debug')
            ->with('[Payum] 1# '.get_class($action).'::execute(NamespacedRequest)')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPreExecute($stdRequest);

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
            ->with('[Payum] 1# '.get_class($action).'::execute(CaptureRequest{model: ArrayObject})')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPreExecute($modelRequest);
        $extension->onExecute($modelRequest, $action);
    }

    /**
     * @test
     */
    public function shouldLogActionAndModelRequestWithObjectModelOnExecute()
    {
        $action = new FooAction;
        $stdModel = new \stdClass;
        $stdModelRequest = new CaptureRequest($stdModel);

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum] 1# '.get_class($action).'::execute(CaptureRequest{model: stdClass})')
        ;

        $extension = new LogExecutedActionsExtension($logger);

        $extension->onPreExecute($stdModelRequest);
        $extension->onExecute($stdModelRequest, $action);
    }

    /**
     * @test
     */
    public function shouldLogOnInteractiveRequest()
    {
        $action = new FooAction;
        $interactiveRequest = $this->createInteractiveRequestMock();

        $ro = new \ReflectionObject($interactiveRequest);

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[Payum] 1# FooAction::execute(string) throws interactive '.$ro->getShortName())
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
            ->with('[Payum] 1# FooAction::execute(string) throws interactive RedirectUrlInteractiveRequest{url: '.$interactiveRequest->getUrl().'}')
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
            ->with('[Payum] 1# FooAction::execute(string) throws exception LogicException')
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
            ->with('[Payum] 1# Payment::execute(string) throws exception LogicException')
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