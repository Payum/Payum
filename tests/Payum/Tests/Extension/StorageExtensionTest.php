<?php
namespace Payum\Tests\Extension;

use Payum\Extension\StorageExtension;
use Payum\Storage\StorageInterface;

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
    public function shouldSetFirstRequestOnPreExecute()
    {
        $extension = new StorageExtension($this->createStorageMock());

        $expectedFirstRequest = new \stdClass;
        
        $extension->onPreExecute($expectedFirstRequest);
        $this->assertAttributeSame($expectedFirstRequest, 'firstRequest', $extension);
    }

    /**
     * @test
     */
    public function shouldNotChangeFirstRequestIfAlreadySetOnPreExecute()
    {
        $extension = new StorageExtension($this->createStorageMock());

        $expectedFirstRequest = new \stdClass;
        $otherRequest = new \stdClass;

        $extension->onPreExecute($expectedFirstRequest);
        $extension->onPreExecute($otherRequest);
        
        $this->assertAttributeSame($expectedFirstRequest, 'firstRequest', $extension);
    }

    /**
     * @test
     */
    public function shouldSetFoundModelOnFirstRequestIfIdGivenAsModelAndStorageSupportsIt()
    {
        $expectedModel = new \stdClass;
        $expectedId = 123;

        $storageMock = $this->createStorageMock();
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
            ->will($this->returnValue($expectedId))
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
    public function shouldDoNothingIfNotFirstRequest()
    {
        $modelId = 123;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->never())
            ->method('findModelById')
        ;

        $modelRequestMock = $this->getMock('Payum\Request\ModelRequestInterface');
        $modelRequestMock
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($modelId))
        ;
        $modelRequestMock
            ->expects($this->never())
            ->method('setModel')
        ;

        $extension = new StorageExtension($storageMock);

        $extension->onPreExecute(new \stdClass);
        $extension->onPreExecute($modelRequestMock);
    }

    /**
     * @test
     */
    public function shouldSetFirstRequestToNullOnPostExecute()
    {
        $extension = new StorageExtension($this->createStorageMock());

        $firstRequest = new \stdClass;

        $extension->onPreExecute($firstRequest);

        $extension->onPostExecute($firstRequest, $this->createActionMock());
        $this->assertAttributeEquals(null, 'firstRequest', $extension);
    }

    /**
     * @test
     */
    public function shouldNotChangeFirstRequestIfRequestNotFirstOnPostExecute()
    {
        $extension = new StorageExtension($this->createStorageMock());

        $firstRequest = new \stdClass;
        $otherRequest = new \stdClass;

        $extension->onPreExecute($firstRequest);
        $extension->onPostExecute($otherRequest, $this->createActionMock());

        $this->assertAttributeSame($firstRequest, 'firstRequest', $extension);
    }

    /**
     * @test
     */
    public function shouldSetFirstRequestToNullOnInteractiveRequest()
    {
        $extension = new StorageExtension($this->createStorageMock());

        $firstRequest = new \stdClass;

        $extension->onPreExecute($firstRequest);

        $extension->onInteractiveRequest($this->createInteractiveRequestMock(), $firstRequest, $this->createActionMock());
        $this->assertAttributeEquals(null, 'firstRequest', $extension);
    }

    /**
     * @test
     */
    public function shouldNotChangeFirstRequestIfRequestNotFirstOnInteractiveRequest()
    {
        $extension = new StorageExtension($this->createStorageMock());

        $firstRequest = new \stdClass;
        $otherRequest = new \stdClass;

        $extension->onPreExecute($firstRequest);
        $extension->onInteractiveRequest($this->createInteractiveRequestMock(), $otherRequest, $this->createActionMock());

        $this->assertAttributeSame($firstRequest, 'firstRequest', $extension);
    }

    /**
     * @test
     */
    public function shouldSetFirstRequestToNullOnException()
    {
        $extension = new StorageExtension($this->createStorageMock());

        $firstRequest = new \stdClass;

        $extension->onPreExecute($firstRequest);

        $extension->onException(new \Exception, $firstRequest);
        
        $this->assertAttributeEquals(null, 'firstRequest', $extension);
    }

    /**
     * @test
     */
    public function shouldNotChangeFirstRequestIfRequestNotFirstOnException()
    {
        $extension = new StorageExtension($this->createStorageMock());

        $firstRequest = new \stdClass;
        $otherRequest = new \stdClass;

        $extension->onPreExecute($firstRequest);
        $extension->onException(new \Exception, $otherRequest);

        $this->assertAttributeSame($firstRequest, 'firstRequest', $extension);
    }

    /**
     * @test
     */
    public function shouldUpdateModelOnPostRequest()
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
        
        $extension->onPostExecute($modelRequestMock, $this->createActionMock());
    }

    /**
     * @test
     */
    public function shouldNotUpdateModelOnPostRequestExecuteIfStorageNotSupportIt()
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

        $extension->onPostExecute($modelRequestMock, $this->createActionMock());
    }

    /**
     * @test
     */
    public function shouldUpdateModelOnInteractiveRequest()
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

        $extension->onInteractiveRequest($this->createInteractiveRequestMock(), $modelRequestMock, $this->createActionMock());
    }

    /**
     * @test
     */
    public function shouldNotUpdateModelOnInteractiveRequestIfStorageNotSupportIt()
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

        $extension->onInteractiveRequest($this->createInteractiveRequestMock(), $modelRequestMock, $this->createActionMock());
    }

    /**
     * @test
     */
    public function shouldUpdateModelOnException()
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

        $extension->onException(new \Exception, $modelRequestMock);
    }

    /**
     * @test
     */
    public function shouldNotUpdateModelOnExceptionIfStorageNotSupportIt()
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
     * @return \PHPUnit_Framework_MockObject_MockObject|StorageInterface
     */
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