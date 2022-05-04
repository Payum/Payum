<?php
namespace Payum\Core\Tests\Action;

use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Model\DetailsAggregateInterface;
use Payum\Core\Model\DetailsAwareInterface;
use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;
use Payum\Core\Tests\GenericActionTest;

class ExecuteSameRequestWithModelDetailsActionTest extends GenericActionTest
{
    protected $actionClass = 'Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction';

    protected $requestClass = 'Payum\Core\Tests\Action\ModelAggregateAwareRequest';

    public function provideSupportedRequests(): \Iterator
    {
        yield array(new $this->requestClass(new DetailsAggregateAndAwareModel()));
        yield array(new $this->requestClass(new DetailsAggregateModel()));
    }

    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass($this->actionClass);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldExecuteSameRequestWithModelDetails()
    {
        $expectedDetails = new \stdClass();

        $model = new DetailsAggregateModel();
        $model->details = $expectedDetails;

        $request = new ModelAggregateAwareRequest($model);

        $testCase = $this;

        $gatewayMock = $this->createMock('Payum\Core\GatewayInterface');
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($request))
            ->willReturnCallback(function ($request) use ($expectedDetails, $testCase) {
                $testCase->assertSame($expectedDetails, $request->getModel());
            })
        ;

        $action = new ExecuteSameRequestWithModelDetailsAction();
        $action->setGateway($gatewayMock);

        $action->execute($request);

        $this->assertSame($expectedDetails, $model->getDetails());
    }

    public function testShouldWrapArrayDetailsToArrayObjectAndExecute()
    {
        $expectedDetails = array('foo' => 'fooVal', 'bar' => 'barVal');

        $model = new DetailsAggregateModel();
        $model->details = $expectedDetails;

        $request = new ModelAggregateAwareRequest($model);

        $testCase = $this;

        $gatewayMock = $this->createMock('Payum\Core\GatewayInterface');
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($request))
            ->willReturnCallback(function ($request) use ($expectedDetails, $testCase) {
                $details = $request->getModel();

                $testCase->assertInstanceOf('ArrayAccess', $details);
                $testCase->assertSame($expectedDetails, iterator_to_array($details));

                $details['baz'] = 'bazVal';
            })
        ;

        $action = new ExecuteSameRequestWithModelDetailsAction();
        $action->setGateway($gatewayMock);

        $action->execute($request);

        $details = $model->getDetails();
        $this->assertEquals($details, $model->getDetails());
    }

    public function testShouldWrapArrayDetailsToArrayObjectAndSetDetailsBackAfterExecution()
    {
        $expectedDetails = array('foo' => 'fooVal', 'bar' => 'barVal');

        $model = new DetailsAggregateAndAwareModel();
        $model->details = $expectedDetails;

        $request = new ModelAggregateAwareRequest($model);

        $testCase = $this;

        $gatewayMock = $this->createMock('Payum\Core\GatewayInterface');
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($request))
            ->willReturnCallback(function ($request) use ($expectedDetails, $testCase) {
                $details = $request->getModel();

                $testCase->assertInstanceOf('ArrayAccess', $details);
                $testCase->assertSame($expectedDetails, iterator_to_array($details));

                $details['baz'] = 'bazVal';
            })
        ;

        $action = new ExecuteSameRequestWithModelDetailsAction();
        $action->setGateway($gatewayMock);

        $action->execute($request);

        $details = $model->getDetails();
        $testCase->assertInstanceOf('ArrayAccess', $details);
        $testCase->assertSame(
            array('foo' => 'fooVal', 'bar' => 'barVal', 'baz' => 'bazVal'),
            iterator_to_array($details)
        );
    }

    public function testShouldWrapArrayDetailsToArrayObjectAndSetDetailsBackEvenOnException()
    {
        $expectedDetails = array('foo' => 'fooVal', 'bar' => 'barVal');

        $model = new DetailsAggregateAndAwareModel();
        $model->details = $expectedDetails;

        $request = new ModelAggregateAwareRequest($model);

        $testCase = $this;

        $gatewayMock = $this->createMock('Payum\Core\GatewayInterface');
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($request))
            ->willReturnCallback(function ($request) use ($expectedDetails, $testCase) {
                $details = $request->getModel();

                $testCase->assertInstanceOf('ArrayAccess', $details);
                $testCase->assertSame($expectedDetails, iterator_to_array($details));

                $details['baz'] = 'bazVal';

                throw new \LogicException('The exception');
            })
        ;

        $action = new ExecuteSameRequestWithModelDetailsAction();
        $action->setGateway($gatewayMock);

        try {
            $action->execute($request);
        } catch (\LogicException $e) {
            $details = $model->getDetails();
            $testCase->assertInstanceOf('ArrayAccess', $details);
            $testCase->assertSame(
                array('foo' => 'fooVal', 'bar' => 'barVal', 'baz' => 'bazVal'),
                iterator_to_array($details)
            );

            return;
        }

        $this->fail('The exception is expected to be thrown');
    }
}

class ModelAggregateAwareRequest implements ModelAwareInterface, ModelAggregateInterface
{
    public $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function setModel($model)
    {
        $this->model = $model;
    }
}

class DetailsAggregateModel implements DetailsAggregateInterface
{
    public $details = array();

    public function getDetails()
    {
        return $this->details;
    }
}

class DetailsAggregateAndAwareModel implements DetailsAggregateInterface, DetailsAwareInterface
{
    public $details = array();

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }
}
