<?php
namespace Payum\Tests\Extension;

use Payum\Extension\StorageExtension;
use Payum\Storage\Identificator;
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
    public function shouldDoNothingOnPreExecuteIfModelNotIdentificator()
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
            ->with(get_class($expectedModel))
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