<?php

namespace Payum\Core\Tests\Action;

use ArrayAccess;
use Iterator;
use LogicException;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\DetailsAggregateInterface;
use Payum\Core\Model\DetailsAwareInterface;
use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;
use Payum\Core\Tests\GenericActionTest;
use ReflectionClass;
use stdClass;

class ExecuteSameRequestWithModelDetailsActionTest extends GenericActionTest
{
    protected $actionClass = ExecuteSameRequestWithModelDetailsAction::class;

    protected $requestClass = ModelAggregateAwareRequest::class;

    public function provideSupportedRequests(): Iterator
    {
        yield [new $this->requestClass(new DetailsAggregateAndAwareModel())];
        yield [new $this->requestClass(new DetailsAggregateModel())];
    }

    public function testShouldImplementGatewayAwareInterface(): void
    {
        $rc = new ReflectionClass($this->actionClass);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldExecuteSameRequestWithModelDetails(): void
    {
        $expectedDetails = new stdClass();

        $model = new DetailsAggregateModel();
        $model->details = $expectedDetails;

        $request = new ModelAggregateAwareRequest($model);

        $testCase = $this;

        $gatewayMock = $this->createMock(GatewayInterface::class);
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($request))
            ->willReturnCallback(function ($request) use ($expectedDetails, $testCase): void {
                $testCase->assertSame($expectedDetails, $request->getModel());
            })
        ;

        $action = new ExecuteSameRequestWithModelDetailsAction();
        $action->setGateway($gatewayMock);

        $action->execute($request);

        $this->assertSame($expectedDetails, $model->getDetails());
    }

    public function testShouldWrapArrayDetailsToArrayObjectAndExecute(): void
    {
        $expectedDetails = [
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ];

        $model = new DetailsAggregateModel();
        $model->details = $expectedDetails;

        $request = new ModelAggregateAwareRequest($model);

        $gatewayMock = $this->createMock(GatewayInterface::class);
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($request))
            ->willReturnCallback(function ($request) use ($expectedDetails): void {
                $details = $request->getModel();

                $this->assertInstanceOf(ArrayAccess::class, $details);
                $this->assertSame($expectedDetails, (array) $details);

                $details['baz'] = 'bazVal';
            })
        ;

        $action = new ExecuteSameRequestWithModelDetailsAction();
        $action->setGateway($gatewayMock);

        $action->execute($request);

        $details = $model->getDetails();
        $this->assertSame($details, $model->getDetails());
    }

    public function testShouldWrapArrayDetailsToArrayObjectAndSetDetailsBackAfterExecution(): void
    {
        $expectedDetails = [
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ];

        $model = new DetailsAggregateAndAwareModel();
        $model->details = $expectedDetails;

        $request = new ModelAggregateAwareRequest($model);

        $testCase = $this;

        $gatewayMock = $this->createMock(GatewayInterface::class);
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($request))
            ->willReturnCallback(function ($request) use ($expectedDetails, $testCase): void {
                $details = $request->getModel();

                $testCase->assertInstanceOf(ArrayAccess::class, $details);
                $testCase->assertSame($expectedDetails, (array) $details);

                $details['baz'] = 'bazVal';
            })
        ;

        $action = new ExecuteSameRequestWithModelDetailsAction();
        $action->setGateway($gatewayMock);

        $action->execute($request);

        $details = $model->getDetails();
        $testCase->assertInstanceOf(ArrayAccess::class, $details);
        $testCase->assertSame(
            [
                'foo' => 'fooVal',
                'bar' => 'barVal',
                'baz' => 'bazVal',
            ],
            (array) $details
        );
    }

    public function testShouldWrapArrayDetailsToArrayObjectAndSetDetailsBackEvenOnException(): void
    {
        $expectedDetails = [
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ];

        $model = new DetailsAggregateAndAwareModel();
        $model->details = $expectedDetails;

        $request = new ModelAggregateAwareRequest($model);

        $testCase = $this;

        $gatewayMock = $this->createMock(GatewayInterface::class);
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($request))
            ->willReturnCallback(function ($request) use ($expectedDetails, $testCase): void {
                $details = $request->getModel();

                $testCase->assertInstanceOf(ArrayAccess::class, $details);
                $testCase->assertSame($expectedDetails, (array) $details);

                $details['baz'] = 'bazVal';

                throw new LogicException('The exception');
            })
        ;

        $action = new ExecuteSameRequestWithModelDetailsAction();
        $action->setGateway($gatewayMock);

        try {
            $action->execute($request);
        } catch (LogicException) {
            $details = $model->getDetails();
            $testCase->assertInstanceOf(ArrayAccess::class, $details);
            $testCase->assertSame(
                [
                    'foo' => 'fooVal',
                    'bar' => 'barVal',
                    'baz' => 'bazVal',
                ],
                (array) $details
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

    public function setModel($model): void
    {
        $this->model = $model;
    }
}

class DetailsAggregateModel implements DetailsAggregateInterface
{
    public $details = [];

    public function getDetails()
    {
        return $this->details;
    }
}

class DetailsAggregateAndAwareModel implements DetailsAggregateInterface, DetailsAwareInterface
{
    public $details = [];

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details): void
    {
        $this->details = $details;
    }
}
