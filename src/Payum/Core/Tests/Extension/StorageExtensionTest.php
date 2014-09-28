<?php
namespace Payum\Core\Tests\Extension;

use Payum\Core\Extension\StorageExtension;
use Payum\Core\Model\Identificator;

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
            ->method('supportModel')
            ->will($this->returnValue(false))
        ;
        $neverUsedStorageMock
            ->expects($this->never())
            ->method('findModelById')
        ;

        $request = new \stdClass;

        $extension = new StorageExtension($neverUsedStorageMock);

        $extension->onPreExecute($request);
    }

    /**
     * @test
     */
    public function shouldDoNothingOnPreExecuteIfFindModelByIdentificatorReturnNull()
    {
        $expectedModel = new \stdClass;
        $expectedId = 123;
        $identificator = new Identificator($expectedId, $expectedModel);

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->atLeastOnce())
            ->method('findModelByIdentificator')
            ->with($identificator)
            ->will($this->returnValue(null))
        ;

        $modelRequestMock = $this->getMock('Payum\Core\Model\ModelAggregateInterface');
        $modelRequestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($identificator))
        ;
        $modelRequestMock
            ->expects($this->never())
            ->method('setModel')
        ;

        $extension = new StorageExtension($storageMock);

        $extension->onPreExecute($modelRequestMock);
    }

    /**
     * @test
     */
    public function shouldDoNothingOnPreExecuteIfModelNotIdentificatorAndNotSupported()
    {
        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->atLeastOnce())
            ->method('supportModel')
            ->will($this->returnValue(true))
        ;
        $storageMock
            ->expects($this->never())
            ->method('findModelById')
        ;

        $modelRequestMock = $this->getMock('Payum\Core\Model\ModelAggregateInterface');
        $modelRequestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue(new \stdClass))
        ;
        $modelRequestMock
            ->expects($this->never())
            ->method('setModel')
        ;

        $extension = new StorageExtension($storageMock);

        $extension->onPreExecute($modelRequestMock);
    }

    /**
     * @test
     */
    public function shouldDoNothingOnPreExecuteIfRequestNotModelRequest()
    {
        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->never())
            ->method('supportModel')
        ;
        $storageMock
            ->expects($this->never())
            ->method('findModelById')
        ;

        $notModelRequestMock = $this->getMock('Payum\Core\Request\RequestInterface');

        $extension = new StorageExtension($storageMock);

        $extension->onPreExecute($notModelRequestMock);
    }

    /**
     * @test
     */
    public function shouldSetFoundModelOnRequestIfIdentifierGivenAsModelAndStorageSupportsIt()
    {
        $expectedModel = new \stdClass;
        $expectedId = 123; 
        $identificator = new Identificator($expectedId, $expectedModel);

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('FindModelByIdentificator')
            ->with($identificator)
            ->will($this->returnValue($expectedModel))
        ;
        
        $modelRequestMock = $this->getMock('Payum\Core\Request\Generic', array(), array(), '', false);
        $modelRequestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($identificator))
        ;
        $modelRequestMock
            ->expects($this->any())
            ->method('setModel')
            ->with($this->identicalTo($expectedModel))
        ;

        $extension = new StorageExtension($storageMock);

        $extension->onPreExecute($modelRequestMock);
    }

    /**
     * @test
     */
    public function shouldScheduleForUpdateRequestModelIfStorageSupportItOnPreExecute()
    {
        $model = new \stdClass;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->never())
            ->method('findModelByIdentificator')
        ;
        $storageMock
            ->expects($this->once())
            ->method('supportModel')
            ->with($this->identicalTo($model))
            ->will($this->returnValue(true))
        ;

        $modelRequestMock = $this->getMock('Payum\Core\Model\ModelAggregateInterface', array('getModel', 'setModel'));
        $modelRequestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($model))
        ;

        $extension = new StorageExtension($storageMock);

        $this->assertAttributeCount(0, 'scheduledForUpdateModels', $extension);

        $extension->onPreExecute($modelRequestMock);

        $this->assertAttributeCount(1, 'scheduledForUpdateModels', $extension);
        $this->assertAttributeContains($model, 'scheduledForUpdateModels', $extension);
    }

    /**
     * @test
     */
    public function shouldScheduleForUpdateRequestModelIfStorageSupportItOnPostExecute()
    {
        $model = new \stdClass;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->never())
            ->method('findModelByIdentificator')
        ;
        $storageMock
            ->expects($this->once())
            ->method('supportModel')
            ->with($this->identicalTo($model))
            ->will($this->returnValue(true))
        ;

        $modelRequestMock = $this->getMock('Payum\Core\Model\ModelAggregateInterface', array('getModel', 'setModel'));
        $modelRequestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($model))
        ;

        $extension = new StorageExtension($storageMock);

        $this->assertAttributeCount(0, 'scheduledForUpdateModels', $extension);

        $extension->onPostExecute($modelRequestMock, $this->createActionMock());

        $this->assertAttributeCount(1, 'scheduledForUpdateModels', $extension);
        $this->assertAttributeContains($model, 'scheduledForUpdateModels', $extension);
    }

    /**
     * @test
     */
    public function shouldScheduleForUpdateRequestModelIfStorageSupportItOnReply()
    {
        $model = new \stdClass;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->never())
            ->method('findModelByIdentificator')
        ;
        $storageMock
            ->expects($this->once())
            ->method('supportModel')
            ->with($this->identicalTo($model))
            ->will($this->returnValue(true))
        ;

        $modelRequestMock = $this->getMock('Payum\Core\Model\ModelAggregateInterface', array('getModel', 'setModel'));
        $modelRequestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($model))
        ;

        $extension = new StorageExtension($storageMock);

        $this->assertAttributeCount(0, 'scheduledForUpdateModels', $extension);

        $extension->onReply($this->createReplyMock(), $modelRequestMock, $this->createActionMock());

        $this->assertAttributeCount(1, 'scheduledForUpdateModels', $extension);
        $this->assertAttributeContains($model, 'scheduledForUpdateModels', $extension);
    }

    /**
     * @test
     */
    public function shouldScheduleForUpdateRequestModelIfStorageSupportItOnException()
    {
        $model = new \stdClass;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->never())
            ->method('findModelByIdentificator')
        ;
        $storageMock
            ->expects($this->once())
            ->method('supportModel')
            ->with($this->identicalTo($model))
            ->will($this->returnValue(true))
        ;

        $modelRequestMock = $this->getMock('Payum\Core\Model\ModelAggregateInterface', array('getModel', 'setModel'));
        $modelRequestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($model))
        ;

        $extension = new StorageExtension($storageMock);

        $this->assertAttributeCount(0, 'scheduledForUpdateModels', $extension);

        $extension->onException(new \Exception, $modelRequestMock, $this->createActionMock());

        $this->assertAttributeCount(1, 'scheduledForUpdateModels', $extension);
        $this->assertAttributeContains($model, 'scheduledForUpdateModels', $extension);
    }

    /**
     * @test
     */
    public function shouldIncreaseStackLevelOnEveryOnPreExecuteCall()
    {
        $extension = new StorageExtension($this->createStorageMock());

        $this->assertAttributeEquals(0, 'stackLevel', $extension);
        
        $extension->onPreExecute(new \stdClass);
        $this->assertAttributeEquals(1, 'stackLevel', $extension);

        $extension->onPreExecute(new \stdClass);
        $this->assertAttributeEquals(2, 'stackLevel', $extension);

        $extension->onPreExecute(new \stdClass);
        $this->assertAttributeEquals(3, 'stackLevel', $extension);
    }

    /**
     * @test
     */
    public function shouldDecreaseStackLevelOnEveryOnPostExecuteCall()
    {
        $extension = new StorageExtension($this->createStorageMock());

        $extension->onPreExecute(new \stdClass);
        $extension->onPreExecute(new \stdClass);
        $extension->onPreExecute(new \stdClass);
        
        //guard
        $this->assertAttributeEquals(3, 'stackLevel', $extension);

        $extension->onPostExecute(new \stdClass, $this->createActionMock());
        $this->assertAttributeEquals(2, 'stackLevel', $extension);
        
        $extension->onPostExecute(new \stdClass, $this->createActionMock());
        $this->assertAttributeEquals(1, 'stackLevel', $extension);
    }

    /**
     * @test
     */
    public function shouldDecreaseStackLevelOnEveryOnReply()
    {
        $extension = new StorageExtension($this->createStorageMock());

        $extension->onPreExecute(new \stdClass);
        $extension->onPreExecute(new \stdClass);
        $extension->onPreExecute(new \stdClass);

        //guard
        $this->assertAttributeEquals(3, 'stackLevel', $extension);

        $extension->onReply($this->createReplyMock(), new \stdClass, $this->createActionMock());
        $this->assertAttributeEquals(2, 'stackLevel', $extension);

        $extension->onReply($this->createReplyMock(), new \stdClass, $this->createActionMock());
        $this->assertAttributeEquals(1, 'stackLevel', $extension);
    }

    /**
     * @test
     */
    public function shouldDecreaseStackLevelOnEveryOnExceptionCall()
    {
        $extension = new StorageExtension($this->createStorageMock());

        $extension->onPreExecute(new \stdClass);
        $extension->onPreExecute(new \stdClass);
        $extension->onPreExecute(new \stdClass);

        //guard
        $this->assertAttributeEquals(3, 'stackLevel', $extension);

        $extension->onException(new \Exception, new \stdClass);
        $this->assertAttributeEquals(2, 'stackLevel', $extension);

        $extension->onException(new \Exception, new \stdClass, $this->createActionMock());
        $this->assertAttributeEquals(1, 'stackLevel', $extension);
    }

    /**
     * @test
     */
    public function shouldUpdateModelOneTimeOnLastStackLevelOnPostExecute()
    {
        $expectedModel = new \stdClass;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->atLeastOnce())
            ->method('supportModel')
            ->with($this->identicalTo($expectedModel))
            ->will($this->returnValue(true))
        ;
        $storageMock
            ->expects($this->once())
            ->method('updateModel')
            ->with($this->identicalTo($expectedModel))
        ;

        $modelRequestMock = $this->getMock('Payum\Core\Model\ModelAggregateInterface');
        $modelRequestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($expectedModel))
        ;

        $extension = new StorageExtension($storageMock);

        $extension->onPreExecute($modelRequestMock);
        $extension->onPreExecute($modelRequestMock);
        $extension->onPreExecute($modelRequestMock);

        //guard
        $this->assertAttributeEquals(3, 'stackLevel', $extension);
        $this->assertAttributeNotEmpty('scheduledForUpdateModels', $extension);

        $extension->onPostExecute(new \stdClass, $this->createActionMock());
        $this->assertAttributeNotEmpty('scheduledForUpdateModels', $extension);

        $extension->onPostExecute(new \stdClass, $this->createActionMock());
        $this->assertAttributeNotEmpty('scheduledForUpdateModels', $extension);

        $extension->onPostExecute(new \stdClass, $this->createActionMock());
        $this->assertAttributeEmpty('scheduledForUpdateModels', $extension);
    }

    /**
     * @test
     */
    public function shouldUpdateModelOneTimeOnLastStackLevelOnReply()
    {
        $expectedModel = new \stdClass;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->atLeastOnce())
            ->method('supportModel')
            ->with($this->identicalTo($expectedModel))
            ->will($this->returnValue(true))
        ;
        $storageMock
            ->expects($this->once())
            ->method('updateModel')
            ->with($this->identicalTo($expectedModel))
        ;

        $modelRequestMock = $this->getMock('Payum\Core\Model\ModelAggregateInterface');
        $modelRequestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($expectedModel))
        ;

        $extension = new StorageExtension($storageMock);

        $extension->onPreExecute($modelRequestMock);
        $extension->onPreExecute($modelRequestMock);
        $extension->onPreExecute($modelRequestMock);

        //guard
        $this->assertAttributeEquals(3, 'stackLevel', $extension);
        $this->assertAttributeNotEmpty('scheduledForUpdateModels', $extension);

        $extension->onReply($this->createReplyMock(), $modelRequestMock, $this->createActionMock());
        $this->assertAttributeNotEmpty('scheduledForUpdateModels', $extension);

        $extension->onReply($this->createReplyMock(), $modelRequestMock, $this->createActionMock());
        $this->assertAttributeNotEmpty('scheduledForUpdateModels', $extension);

        $extension->onReply($this->createReplyMock(), $modelRequestMock, $this->createActionMock());
        $this->assertAttributeEmpty('scheduledForUpdateModels', $extension);
    }

    /**
     * @test
     */
    public function shouldUpdateModelOneTimeOnLastStackLevelOnException()
    {
        $expectedModel = new \stdClass;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->atLeastOnce())
            ->method('supportModel')
            ->with($this->identicalTo($expectedModel))
            ->will($this->returnValue(true))
        ;
        $storageMock
            ->expects($this->once())
            ->method('updateModel')
            ->with($this->identicalTo($expectedModel))
        ;

        $modelRequestMock = $this->getMock('Payum\Core\Model\ModelAggregateInterface');
        $modelRequestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($expectedModel))
        ;

        $extension = new StorageExtension($storageMock);

        $extension->onPreExecute($modelRequestMock);
        $extension->onPreExecute($modelRequestMock);
        $extension->onPreExecute($modelRequestMock);

        //guard
        $this->assertAttributeEquals(3, 'stackLevel', $extension);
        $this->assertAttributeNotEmpty('scheduledForUpdateModels', $extension);

        $extension->onException(new \Exception, new \stdClass, $this->createActionMock());
        $this->assertAttributeNotEmpty('scheduledForUpdateModels', $extension);

        $extension->onException(new \Exception, new \stdClass, $this->createActionMock());
        $this->assertAttributeNotEmpty('scheduledForUpdateModels', $extension);

        $extension->onException(new \Exception, new \stdClass, $this->createActionMock());
        $this->assertAttributeEmpty('scheduledForUpdateModels', $extension);
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

    protected function createStorageMock()
    {
        return $this->getMock('Payum\Core\Storage\StorageInterface');
    }

    protected function createActionMock()
    {
        return $this->getMock('Payum\Core\Action\ActionInterface');
    }

    protected function createReplyMock()
    {
        return $this->getMock('Payum\Core\Reply\ReplyInterface');
    }
}