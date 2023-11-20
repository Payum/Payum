<?php

namespace Payum\Core\Tests\Extension;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Extension\StorageExtension;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Identity;
use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use stdClass;

class StorageExtensionTest extends TestCase
{
    public function testShouldImplementExtensionInterface(): void
    {
        $rc = new ReflectionClass(StorageExtension::class);

        $this->assertTrue($rc->implementsInterface(ExtensionInterface::class));
    }

    public function testShouldDoNothingOnPreExecuteIfNoModelRequest(): void
    {
        $neverUsedStorageMock = $this->createStorageMock();
        $neverUsedStorageMock
            ->expects($this->never())
            ->method('support')
            ->willReturn(false)
        ;
        $neverUsedStorageMock
            ->expects($this->never())
            ->method('find')
        ;

        $context = new Context($this->createGatewayMock(), new stdClass(), []);

        $extension = new StorageExtension($neverUsedStorageMock);

        $extension->onPreExecute($context);
    }

    public function testShouldDoNothingOnPreExecuteIfFindModelByIdentityReturnNull(): void
    {
        $expectedModel = new stdClass();
        $expectedId = 123;
        $identity = new Identity($expectedId, $expectedModel);

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->atLeastOnce())
            ->method('find')
            ->with($identity)
            ->willReturn(null)
        ;

        $requestMock = $this->createMock(ModelAggregateAndAwareInterface::class);
        $requestMock
            ->method('getModel')
            ->willReturn($identity)
        ;
        $requestMock
            ->expects($this->never())
            ->method('setModel')
        ;

        $context = new Context($this->createGatewayMock(), $requestMock, []);

        $extension = new StorageExtension($storageMock);

        $extension->onPreExecute($context);
    }

    public function testShouldDoNothingOnPreExecuteIfModelNotIdentityAndNotSupported(): void
    {
        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->atLeastOnce())
            ->method('support')
            ->willReturn(true)
        ;
        $storageMock
            ->expects($this->never())
            ->method('find')
        ;

        $requestMock = $this->createMock(ModelAggregateAndAwareInterface::class);
        $requestMock
            ->method('getModel')
            ->willReturn(new stdClass())
        ;
        $requestMock
            ->expects($this->never())
            ->method('setModel')
        ;

        $context = new Context($this->createGatewayMock(), $requestMock, []);

        $extension = new StorageExtension($storageMock);

        $extension->onPreExecute($context);
    }

    public function testShouldDoNothingOnPreExecuteIfRequestNotModelRequest(): void
    {
        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->never())
            ->method('support')
        ;
        $storageMock
            ->expects($this->never())
            ->method('find')
        ;

        $requestMock = $this->createMock(stdClass::class);

        $context = new Context($this->createGatewayMock(), $requestMock, []);

        $extension = new StorageExtension($storageMock);

        $extension->onPreExecute($context);
    }

    public function testShouldSetFoundModelOnRequestIfIdentifierGivenAsModelAndStorageSupportsIt(): void
    {
        $expectedModel = new stdClass();
        $expectedId = 123;
        $identity = new Identity($expectedId, $expectedModel);

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('find')
            ->with($identity)
            ->willReturn($expectedModel)
        ;

        $requestMock = $this->createMock(ModelAggregateAndAwareInterface::class);
        $requestMock
            ->method('getModel')
            ->willReturn($identity)
        ;
        $requestMock
            ->method('setModel')
            ->with($this->identicalTo($expectedModel))
        ;

        $context = new Context($this->createGatewayMock(), $requestMock, []);

        $extension = new StorageExtension($storageMock);

        $extension->onPreExecute($context);
    }

    public function testShouldScheduleForUpdateRequestModelIfStorageSupportItOnPreExecute(): void
    {
        $model = new stdClass();

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->never())
            ->method('find')
        ;
        $storageMock
            ->expects($this->once())
            ->method('support')
            ->with($this->identicalTo($model))
            ->willReturn(true)
        ;

        $requestMock = $this->createMock(ModelAggregateInterface::class);
        $requestMock
            ->method('getModel')
            ->willReturn($model)
        ;

        $extension = new StorageExtension($storageMock);

        $context = new Context($this->createGatewayMock(), $requestMock, []);

        $extension->onPreExecute($context);
    }

    public function testShouldScheduleForUpdateRequestModelIfStorageSupportItOnPostExecute(): void
    {
        $model = new stdClass();

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->never())
            ->method('find')
        ;
        $storageMock
            ->expects($this->once())
            ->method('support')
            ->with($this->identicalTo($model))
            ->willReturn(true)
        ;

        $requestMock = $this->createMock(ModelAggregateInterface::class);
        $requestMock
            ->method('getModel')
            ->willReturn($model)
        ;

        $context = new Context($this->createGatewayMock(), $requestMock, [
            new Context($this->createGatewayMock(), $requestMock, []),
        ]);

        $extension = new StorageExtension($storageMock);

        $storageMock
            ->expects($this->never())
            ->method('update')
        ;

        $extension->onPostExecute($context);
    }

    public function testShouldUpdateModelOneTimeOnLatestOnPostExecute(): void
    {
        //when previous is empty

        $expectedModel = new stdClass();

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->atLeastOnce())
            ->method('support')
            ->with($this->identicalTo($expectedModel))
            ->willReturn(true)
        ;
        $storageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($expectedModel))
        ;

        $requestMock = $this->createMock(ModelAggregateInterface::class);
        $requestMock
            ->method('getModel')
            ->willReturn($expectedModel)
        ;

        $extension = new StorageExtension($storageMock);

        $context = new Context($this->createGatewayMock(), $requestMock, []);

        $extension->onPreExecute($context);

        $extension->onPostExecute($context);
    }

    public function testShouldNotUpdateModelIfNotLatestOnPostExecute(): void
    {
        //when previous is NOT empty

        $expectedModel = new stdClass();

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->atLeastOnce())
            ->method('support')
            ->with($this->identicalTo($expectedModel))
            ->willReturn(true)
        ;
        $storageMock
            ->expects($this->never())
            ->method('update')
            ->with($this->identicalTo($expectedModel))
        ;

        $requestMock = $this->createMock(ModelAggregateInterface::class);
        $requestMock
            ->method('getModel')
            ->willReturn($expectedModel)
        ;

        $extension = new StorageExtension($storageMock);

        $previousContext = new Context($this->createGatewayMock(), $requestMock, []);
        $context = new Context($this->createGatewayMock(), $requestMock, [$previousContext]);

        $extension->onPreExecute($context);

        $extension->onPostExecute($context);
    }

    protected function createModelRequestWithModel($model)
    {
        $modelRequestMock = $this->createMock(ModelAggregateAndAwareInterface::class);
        $modelRequestMock
            ->method('getModel')
            ->willReturn($model)
        ;

        return $modelRequestMock;
    }

    /**
     * @return MockObject|StorageInterface<object>
     */
    protected function createStorageMock(): StorageInterface | MockObject
    {
        return $this->createMock(StorageInterface::class);
    }

    protected function createActionMock(): MockObject | ActionInterface
    {
        return $this->createMock(ActionInterface::class);
    }

    protected function createGatewayMock(): GatewayInterface | MockObject
    {
        return $this->createMock(GatewayInterface::class);
    }
}

interface ModelAggregateAndAwareInterface extends ModelAwareInterface, ModelAggregateInterface
{
}
