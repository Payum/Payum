<?php
namespace Payum\Core\Tests\Extension;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\StorageExtension;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Identity;
use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class StorageExtensionTest extends TestCase
{
    public function testShouldImplementExtensionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Extension\StorageExtension');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Extension\ExtensionInterface'));
    }

    public function testShouldDoNothingOnPreExecuteIfNoModelRequest()
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

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());

        $extension = new StorageExtension($neverUsedStorageMock);

        $extension->onPreExecute($context);
    }

    public function testShouldDoNothingOnPreExecuteIfFindModelByIdentityReturnNull()
    {
        $expectedModel = new \stdClass();
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

        $context = new Context($this->createGatewayMock(), $requestMock, array());

        $extension = new StorageExtension($storageMock);

        $extension->onPreExecute($context);
    }

    public function testShouldDoNothingOnPreExecuteIfModelNotIdentityAndNotSupported()
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
            ->willReturn(new \stdClass())
        ;
        $requestMock
            ->expects($this->never())
            ->method('setModel')
        ;

        $context = new Context($this->createGatewayMock(), $requestMock, array());

        $extension = new StorageExtension($storageMock);

        $extension->onPreExecute($context);
    }

    public function testShouldDoNothingOnPreExecuteIfRequestNotModelRequest()
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

        $requestMock = $this->createMock(\stdClass::class);

        $context = new Context($this->createGatewayMock(), $requestMock, array());

        $extension = new StorageExtension($storageMock);

        $extension->onPreExecute($context);
    }

    public function testShouldSetFoundModelOnRequestIfIdentifierGivenAsModelAndStorageSupportsIt()
    {
        $expectedModel = new \stdClass();
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

        $context = new Context($this->createGatewayMock(), $requestMock, array());

        $extension = new StorageExtension($storageMock);

        $extension->onPreExecute($context);
    }

    public function testShouldScheduleForUpdateRequestModelIfStorageSupportItOnPreExecute()
    {
        $model = new \stdClass();

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

        $context = new Context($this->createGatewayMock(), $requestMock, array());

        $extension->onPreExecute($context);
    }

    public function testShouldScheduleForUpdateRequestModelIfStorageSupportItOnPostExecute()
    {
        $model = new \stdClass();

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

        $context = new Context($this->createGatewayMock(), $requestMock, array(
            new Context($this->createGatewayMock(), $requestMock, array())
        ));

        $extension = new StorageExtension($storageMock);

        $storageMock
            ->expects($this->never())
            ->method('update')
        ;

        $extension->onPostExecute($context);
    }

    public function testShouldUpdateModelOneTimeOnLatestOnPostExecute()
    {
        //when previous is empty

        $expectedModel = new \stdClass();

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

        $context = new Context($this->createGatewayMock(), $requestMock, array());

        $extension->onPreExecute($context);

        $extension->onPostExecute($context);
    }

    public function testShouldNotUpdateModelIfNotLatestOnPostExecute()
    {
        //when previous is NOT empty

        $expectedModel = new \stdClass();

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


        $previousContext = new Context($this->createGatewayMock(), $requestMock, array());
        $context = new Context($this->createGatewayMock(), $requestMock, array($previousContext));

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
     * @return MockObject|StorageInterface
     */
    protected function createStorageMock()
    {
        return $this->createMock('Payum\Core\Storage\StorageInterface');
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

interface ModelAggregateAndAwareInterface extends ModelAwareInterface, ModelAggregateInterface
{
}
