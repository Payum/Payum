<?php
namespace Payum\Core\Tests\Extension;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\StorageExtension;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Identity;
use Payum\Core\Storage\StorageInterface;

class StorageExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementExtensionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Extension\StorageExtension');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Extension\ExtensionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithStorageAsArgument()
    {
        new StorageExtension($this->createStorageMock());
    }

    /**
     * @test
     */
    public function shouldDoNothingOnPreExecuteIfNoModelRequest()
    {
        $neverUsedStorageMock = $this->createStorageMock();
        $neverUsedStorageMock
            ->expects($this->never())
            ->method('support')
            ->will($this->returnValue(false))
        ;
        $neverUsedStorageMock
            ->expects($this->never())
            ->method('find')
        ;

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());

        $extension = new StorageExtension($neverUsedStorageMock);

        $extension->onPreExecute($context);
    }

    /**
     * @test
     */
    public function shouldDoNothingOnPreExecuteIfFindModelByIdentityReturnNull()
    {
        $expectedModel = new \stdClass();
        $expectedId = 123;
        $identity = new Identity($expectedId, $expectedModel);

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->atLeastOnce())
            ->method('find')
            ->with($identity)
            ->will($this->returnValue(null))
        ;

        $requestMock = $this->getMock('Payum\Core\Model\ModelAggregateInterface');
        $requestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($identity))
        ;
        $requestMock
            ->expects($this->never())
            ->method('setModel')
        ;

        $context = new Context($this->createGatewayMock(), $requestMock, array());

        $extension = new StorageExtension($storageMock);

        $extension->onPreExecute($context);
    }

    /**
     * @test
     */
    public function shouldDoNothingOnPreExecuteIfModelNotIdentityAndNotSupported()
    {
        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->atLeastOnce())
            ->method('support')
            ->will($this->returnValue(true))
        ;
        $storageMock
            ->expects($this->never())
            ->method('find')
        ;

        $requestMock = $this->getMock('Payum\Core\Model\ModelAggregateInterface');
        $requestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue(new \stdClass()))
        ;
        $requestMock
            ->expects($this->never())
            ->method('setModel')
        ;

        $context = new Context($this->createGatewayMock(), $requestMock, array());

        $extension = new StorageExtension($storageMock);

        $extension->onPreExecute($context);
    }

    /**
     * @test
     */
    public function shouldDoNothingOnPreExecuteIfRequestNotModelRequest()
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

        $requestMock = $this->getMock('Payum\Core\Request\RequestInterface');

        $context = new Context($this->createGatewayMock(), $requestMock, array());

        $extension = new StorageExtension($storageMock);

        $extension->onPreExecute($context);
    }

    /**
     * @test
     */
    public function shouldSetFoundModelOnRequestIfIdentifierGivenAsModelAndStorageSupportsIt()
    {
        $expectedModel = new \stdClass();
        $expectedId = 123;
        $identity = new Identity($expectedId, $expectedModel);

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('find')
            ->with($identity)
            ->will($this->returnValue($expectedModel))
        ;

        $requestMock = $this->getMock('Payum\Core\Request\Generic', array(), array(), '', false);
        $requestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($identity))
        ;
        $requestMock
            ->expects($this->any())
            ->method('setModel')
            ->with($this->identicalTo($expectedModel))
        ;

        $context = new Context($this->createGatewayMock(), $requestMock, array());

        $extension = new StorageExtension($storageMock);

        $extension->onPreExecute($context);
    }

    /**
     * @test
     */
    public function shouldScheduleForUpdateRequestModelIfStorageSupportItOnPreExecute()
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
            ->will($this->returnValue(true))
        ;

        $requestMock = $this->getMock('Payum\Core\Model\ModelAggregateInterface', array('getModel', 'setModel'));
        $requestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($model))
        ;

        $extension = new StorageExtension($storageMock);

        $this->assertAttributeCount(0, 'scheduledForUpdateModels', $extension);

        $context = new Context($this->createGatewayMock(), $requestMock, array());

        $extension->onPreExecute($context);

        $this->assertAttributeCount(1, 'scheduledForUpdateModels', $extension);
        $this->assertAttributeContains($model, 'scheduledForUpdateModels', $extension);
    }

    /**
     * @test
     */
    public function shouldScheduleForUpdateRequestModelIfStorageSupportItOnPostExecute()
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
            ->will($this->returnValue(true))
        ;

        $requestMock = $this->getMock('Payum\Core\Model\ModelAggregateInterface', array('getModel', 'setModel'));
        $requestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($model))
        ;

        $context = new Context($this->createGatewayMock(), $requestMock, array(
            new Context($this->createGatewayMock(), $requestMock, array())
        ));

        $extension = new StorageExtension($storageMock);

        $this->assertAttributeCount(0, 'scheduledForUpdateModels', $extension);

        $extension->onPostExecute($context);

        $this->assertAttributeCount(1, 'scheduledForUpdateModels', $extension);
        $this->assertAttributeContains($model, 'scheduledForUpdateModels', $extension);
    }

    /**
     * @test
     */
    public function shouldUpdateModelOneTimeOnLatestOnPostExecute()
    {
        //when previous is empty

        $expectedModel = new \stdClass();

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->atLeastOnce())
            ->method('support')
            ->with($this->identicalTo($expectedModel))
            ->will($this->returnValue(true))
        ;
        $storageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($expectedModel))
        ;

        $requestMock = $this->getMock('Payum\Core\Model\ModelAggregateInterface');
        $requestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($expectedModel))
        ;

        $extension = new StorageExtension($storageMock);

        $context = new Context($this->createGatewayMock(), $requestMock, array());

        $extension->onPreExecute($context);

        //guard
        $this->assertAttributeNotEmpty('scheduledForUpdateModels', $extension);

        $extension->onPostExecute($context);
        $this->assertAttributeEmpty('scheduledForUpdateModels', $extension);
    }

    /**
     * @test
     */
    public function shouldNotUpdateModelIfNotLatestOnPostExecute()
    {
        //when previous is NOT empty

        $expectedModel = new \stdClass();

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->atLeastOnce())
            ->method('support')
            ->with($this->identicalTo($expectedModel))
            ->will($this->returnValue(true))
        ;
        $storageMock
            ->expects($this->never())
            ->method('update')
            ->with($this->identicalTo($expectedModel))
        ;

        $requestMock = $this->getMock('Payum\Core\Model\ModelAggregateInterface');
        $requestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($expectedModel))
        ;

        $extension = new StorageExtension($storageMock);


        $previousContext = new Context($this->createGatewayMock(), $requestMock, array());
        $context = new Context($this->createGatewayMock(), $requestMock, array($previousContext));

        $extension->onPreExecute($context);

        //guard
        $this->assertAttributeNotEmpty('scheduledForUpdateModels', $extension);

        $extension->onPostExecute($context);
        $this->assertAttributeNotEmpty('scheduledForUpdateModels', $extension);
    }

    protected function createModelRequestWithModel($model)
    {
        $modelRequestMock = $this->getMock('Payum\Core\Model\ModelAggregateInterface', array('setModel', 'getModel'));
        $modelRequestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($model))
        ;

        return $modelRequestMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StorageInterface
     */
    protected function createStorageMock()
    {
        return $this->getMock('Payum\Core\Storage\StorageInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ActionInterface
     */
    protected function createActionMock()
    {
        return $this->getMock('Payum\Core\Action\ActionInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock('Payum\Core\GatewayInterface');
    }
}
