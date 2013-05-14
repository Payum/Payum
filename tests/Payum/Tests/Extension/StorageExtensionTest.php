<?php
namespace Payum\Tests\Extension;

use Payum\Extension\StorageExtension;
use Payum\Storage\Identificator;

class StorageExtensionTest extends \PHPUnit_Framework_TestCase 
{
    /**
     * @test
     */
    public function shouldImplementExtensionInterface()
    {
        $rc = new \ReflectionClass('Payum\Extension\StorageExtension');

        $this->assertTrue($rc->implementsInterface('Payum\Extension\ExtensionInterface'));
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
    public function shouldDoNothingOnPreExecuteIfStorageNotSupportModel()
    {
        $expectedModel = new \stdClass;
        $expectedId = 123;
        $identificator = new Identificator($expectedId, $expectedModel);

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->atLeastOnce())
            ->method('supportModel')
            ->with(get_class($expectedModel))
            ->will($this->returnValue(false))
        ;
        $storageMock
            ->expects($this->never())
            ->method('findModelById')
        ;

        $modelRequestMock = $this->getMock('Payum\Request\ModelRequestInterface');
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

        $modelRequestMock = $this->getMock('Payum\Request\ModelRequestInterface');
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

        $notModelRequestMock = $this->getMock('Payum\Request\RequestInterface');

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
            ->expects($this->atLeastOnce())
            ->method('supportModel')
            ->will($this->returnValue(true))
        ;
        $storageMock
            ->expects($this->once())
            ->method('findModelById')
            ->with($expectedId)
            ->will($this->returnValue($expectedModel))
        ;
        
        $modelRequestMock = $this->getMock('Payum\Request\ModelRequestInterface');
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
    public function shouldIncreaseRequestStackLevelOnEveryOnPreExecuteCall()
    {
        $extension = new StorageExtension($this->createStorageMock());

        $this->assertAttributeEquals(0, 'requestStackLevel', $extension);
        
        $extension->onPreExecute(new \stdClass);
        $this->assertAttributeEquals(1, 'requestStackLevel', $extension);

        $extension->onPreExecute(new \stdClass);
        $this->assertAttributeEquals(2, 'requestStackLevel', $extension);

        $extension->onPreExecute(new \stdClass);
        $this->assertAttributeEquals(3, 'requestStackLevel', $extension);
    }

    /**
     * @test
     */
    public function shouldDecreaseRequestStackLevelOnEveryOnPostExecuteCall()
    {
        $extension = new StorageExtension($this->createStorageMock());

        $extension->onPreExecute(new \stdClass);
        $extension->onPreExecute(new \stdClass);
        $extension->onPreExecute(new \stdClass);
        
        //guard
        $this->assertAttributeEquals(3, 'requestStackLevel', $extension);

        $extension->onPostExecute(new \stdClass, $this->createActionMock());
        $this->assertAttributeEquals(2, 'requestStackLevel', $extension);
        
        $extension->onPostExecute(new \stdClass, $this->createActionMock());
        $this->assertAttributeEquals(1, 'requestStackLevel', $extension);
    }

    /**
     * @test
     */
    public function shouldDecreaseRequestStackLevelOnEveryOnInteractiveRequestCall()
    {
        $extension = new StorageExtension($this->createStorageMock());

        $extension->onPreExecute(new \stdClass);
        $extension->onPreExecute(new \stdClass);
        $extension->onPreExecute(new \stdClass);

        //guard
        $this->assertAttributeEquals(3, 'requestStackLevel', $extension);

        $extension->onInteractiveRequest($this->createInteractiveRequestMock(), new \stdClass, $this->createActionMock());
        $this->assertAttributeEquals(2, 'requestStackLevel', $extension);

        $extension->onInteractiveRequest($this->createInteractiveRequestMock(), new \stdClass, $this->createActionMock());
        $this->assertAttributeEquals(1, 'requestStackLevel', $extension);
    }

    /**
     * @test
     */
    public function shouldSetRequestStackLevelToZeroAndEmptyTrackedModelsOnFirstExceptionCall()
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
            ->expects($this->never())
            ->method('updateModel')
        ;

        $modelRequestMock = $this->getMock('Payum\Request\ModelRequestInterface');
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
        $this->assertAttributeEquals(3, 'requestStackLevel', $extension);
        $this->assertAttributeNotEmpty('trackedModels', $extension);

        $extension->onException(new \Exception, new \stdClass);
        $this->assertAttributeEquals(array(), 'trackedModels', $extension);
        $this->assertAttributeEquals(0, 'requestStackLevel', $extension);
    }

    /**
     * @test
     */
    public function shouldNotUpdateModelOnException()
    {
        $expectedModel = new \stdClass;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->atLeastOnce())
            ->method('supportModel')
            ->with($this->identicalTo($expectedModel))
            ->will($this->returnValue(false))
        ;
        $storageMock
            ->expects($this->never())
            ->method('updateModel')
        ;

        $modelRequestMock = $this->getMock('Payum\Request\ModelRequestInterface');
        $modelRequestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($expectedModel))
        ;

        $extension = new StorageExtension($storageMock);
        $extension->onPreExecute($modelRequestMock);

        $extension->onException(new \Exception, $modelRequestMock);
    }

    /**
     * @test
     */
    public function shouldUpdateModelOneTimeOnSameLevelItIntroducedOnPostExecute()
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

        $modelRequestMock = $this->getMock('Payum\Request\ModelRequestInterface');
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
        $this->assertAttributeEquals(3, 'requestStackLevel', $extension);
        $this->assertAttributeNotEmpty('trackedModels', $extension);

        $extension->onPostExecute(new \stdClass, $this->createActionMock());
        $this->assertAttributeNotEmpty('trackedModels', $extension);

        $extension->onPostExecute(new \stdClass, $this->createActionMock());
        $this->assertAttributeNotEmpty('trackedModels', $extension);

        $extension->onPostExecute(new \stdClass, $this->createActionMock());
        $this->assertAttributeEmpty('trackedModels', $extension);
    }

    /**
     * @test
     */
    public function shouldUpdateModelOneTimeOnSameLevelItIntroducedOnInteractiveRequest()
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

        $modelRequestMock = $this->getMock('Payum\Request\ModelRequestInterface');
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
        $this->assertAttributeEquals(3, 'requestStackLevel', $extension);
        $this->assertAttributeNotEmpty('trackedModels', $extension);

        $extension->onInteractiveRequest($this->createInteractiveRequestMock(), $modelRequestMock, $this->createActionMock());
        $this->assertAttributeNotEmpty('trackedModels', $extension);

        $extension->onInteractiveRequest($this->createInteractiveRequestMock(), $modelRequestMock, $this->createActionMock());
        $this->assertAttributeNotEmpty('trackedModels', $extension);

        $extension->onInteractiveRequest($this->createInteractiveRequestMock(), $modelRequestMock, $this->createActionMock());
        $this->assertAttributeEmpty('trackedModels', $extension);
    }

    protected function createModelRequestWithModel($model)
    {
        $modelRequestMock = $this->getMock('Payum\Request\ModelRequestInterface', array('setModel', 'getModel'));
        $modelRequestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($model))
        ;
        
        return $modelRequestMock;
    }

    protected function createStorageMock()
    {
        return $this->getMock('Payum\Storage\StorageInterface');
    }

    protected function createActionMock()
    {
        return $this->getMock('Payum\Action\ActionInterface');
    }

    protected function createInteractiveRequestMock()
    {
        return $this->getMock('Payum\Request\InteractiveRequestInterface');
    }
}