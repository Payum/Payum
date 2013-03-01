<?php
namespace Payum\Tests\Extension;

use Payum\Extension\ExtensionCollection;
use Payum\Extension\ExtensionInterface;

class ExtensionCollectionTest extends \PHPUnit_Framework_TestCase 
{
    /**
     * @test
     */
    public function shouldImplementExtensionInterface()
    {
        $rc = new \ReflectionClass('Payum\Extension\ExtensionCollection');
        
        $this->assertTrue($rc->implementsInterface('Payum\Extension\ExtensionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new ExtensionCollection;
    }

    /**
     * @test
     */
    public function shouldAllowAddExtensionAppendByDefault()
    {
        $extensionFirst = $this->createExtensionMock();
        $extensionSecond = $this->createExtensionMock();
        
        $collection = new ExtensionCollection;

        $collection->addExtension($extensionFirst);
        $collection->addExtension($extensionSecond);
        
        $addedExtensions = $this->readAttribute($collection, 'extensions');
        
        $this->assertInternalType('array', $addedExtensions);
        $this->assertCount(2, $addedExtensions);
        
        $this->assertSame($extensionFirst, $addedExtensions[0]);
        $this->assertSame($extensionSecond, $addedExtensions[1]);
    }

    /**
     * @test
     */
    public function shouldAllowAddExtensionWithForcedPrepend()
    {
        $extensionFirst = $this->createExtensionMock();
        $extensionSecond = $this->createExtensionMock();

        $collection = new ExtensionCollection;

        $collection->addExtension($extensionFirst);
        $collection->addExtension($extensionSecond, $forcePrepend = true);

        $addedExtensions = $this->readAttribute($collection, 'extensions');

        $this->assertInternalType('array', $addedExtensions);
        $this->assertCount(2, $addedExtensions);

        $this->assertSame($extensionSecond, $addedExtensions[0]);
        $this->assertSame($extensionFirst, $addedExtensions[1]);
    }

    /**
     * @test
     */
    public function shouldCallOnPreExecuteForAllExtensionsInCollection()
    {
        $expectedRequest = new \stdClass;
        
        $extensionFirst = $this->createExtensionMock();
        $extensionFirst
            ->expects($this->once())
            ->method('onPreExecute')
            ->with($this->identicalTo($expectedRequest))
        ;
        
        $extensionSecond = $this->createExtensionMock();
        $extensionSecond
            ->expects($this->once())
            ->method('onPreExecute')
            ->with($this->identicalTo($expectedRequest))
        ;

        $collection = new ExtensionCollection;
        $collection->addExtension($extensionFirst);
        $collection->addExtension($extensionSecond);

        $result = $collection->onPreExecute($expectedRequest);

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function shouldCallOnExecuteForAllExtensionsInCollection()
    {
        $expectedRequest = new \stdClass;
        $expectedAction = $this->getMock('Payum\Action\ActionInterface');

        $extensionFirst = $this->createExtensionMock();
        $extensionFirst
            ->expects($this->once())
            ->method('onExecute')
            ->with(
                $this->identicalTo($expectedRequest),
                $this->identicalTo($expectedAction)
            )
        ;

        $extensionSecond = $this->createExtensionMock();
        $extensionSecond
            ->expects($this->once())
            ->method('onExecute')
            ->with(
                $this->identicalTo($expectedRequest),
                $this->identicalTo($expectedAction)
            )
        ;

        $collection = new ExtensionCollection;
        $collection->addExtension($extensionFirst);
        $collection->addExtension($extensionSecond);

        $result = $collection->onExecute($expectedRequest, $expectedAction);

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function shouldCallOnPostExecuteForAllExtensionsInCollection()
    {
        $expectedRequest = new \stdClass;
        $expectedAction = $this->getMock('Payum\Action\ActionInterface');

        $extensionFirst = $this->createExtensionMock();
        $extensionFirst
            ->expects($this->once())
            ->method('onPostExecute')
            ->with(
                $this->identicalTo($expectedRequest),
                $this->identicalTo($expectedAction)
            )
        ;

        $extensionSecond = $this->createExtensionMock();
        $extensionSecond
            ->expects($this->once())
            ->method('onPostExecute')
            ->with(
                $this->identicalTo($expectedRequest),
                $this->identicalTo($expectedAction)
            )
        ;

        $collection = new ExtensionCollection;
        $collection->addExtension($extensionFirst);
        $collection->addExtension($extensionSecond);

        $result = $collection->onPostExecute($expectedRequest, $expectedAction);

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function shouldCallOnInteractiveRequestForAllExtensionsInCollection()
    {
        $expectedInteractiveRequest = $this->getMock('Payum\Request\InteractiveRequestInterface');
        $expectedAction = $this->getMock('Payum\Action\ActionInterface');
        $expectedRequest = new \stdClass;

        $extensionFirst = $this->createExtensionMock();
        $extensionFirst
            ->expects($this->once())
            ->method('onInteractiveRequest')
            ->with(
                $this->identicalTo($expectedInteractiveRequest),
                $this->identicalTo($expectedRequest),
                $this->identicalTo($expectedAction)
            )
        ;

        $extensionSecond = $this->createExtensionMock();
        $extensionSecond
            ->expects($this->once())
            ->method('onInteractiveRequest')
            ->with(
                $this->identicalTo($expectedInteractiveRequest),
                $this->identicalTo($expectedRequest),
                $this->identicalTo($expectedAction)
            )
        ;

        $collection = new ExtensionCollection;
        $collection->addExtension($extensionFirst);
        $collection->addExtension($extensionSecond);

        $result = $collection->onInteractiveRequest($expectedInteractiveRequest, $expectedRequest, $expectedAction);

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function shouldCallOnInteractiveRequestWithNewRequestIfFirstExtensionReturnNew()
    {
        $expectedInteractiveRequest = $this->getMock('Payum\Request\InteractiveRequestInterface');
        $expectedNewInteractiveRequest = $this->getMock('Payum\Request\InteractiveRequestInterface');
        $expectedAction = $this->getMock('Payum\Action\ActionInterface');
        $expectedRequest = new \stdClass;

        $extensionFirst = $this->createExtensionMock();
        $extensionFirst
            ->expects($this->once())
            ->method('onInteractiveRequest')
            ->with(
                $this->identicalTo($expectedInteractiveRequest),
                $this->identicalTo($expectedRequest),
                $this->identicalTo($expectedAction)
            )
            ->will($this->returnValue($expectedNewInteractiveRequest))
        ;

        $extensionSecond = $this->createExtensionMock();
        $extensionSecond
            ->expects($this->once())
            ->method('onInteractiveRequest')
            ->with(
                $this->identicalTo($expectedNewInteractiveRequest),
                $this->identicalTo($expectedRequest),
                $this->identicalTo($expectedAction)
            )
        ;

        $collection = new ExtensionCollection;
        $collection->addExtension($extensionFirst);
        $collection->addExtension($extensionSecond);

        $result = $collection->onInteractiveRequest($expectedInteractiveRequest, $expectedRequest, $expectedAction);

        $this->assertSame($expectedNewInteractiveRequest, $result);
    }

    /**
     * @test
     */
    public function shouldCallOnExceptionForAllExtensionsInCollection()
    {
        $expectedException = new \Exception;
        $expectedRequest = new \stdClass;
        $expectedAction = $this->getMock('Payum\Action\ActionInterface');

        $extensionFirst = $this->createExtensionMock();
        $extensionFirst
            ->expects($this->once())
            ->method('onException')
            ->with(
                $this->identicalTo($expectedException),
                $this->identicalTo($expectedRequest),
                $this->identicalTo($expectedAction)
            )
        ;

        $extensionSecond = $this->createExtensionMock();
        $extensionSecond
            ->expects($this->once())
            ->method('onException')
            ->with(
                $this->identicalTo($expectedException),
                $this->identicalTo($expectedRequest),
                $this->identicalTo($expectedAction)
            )
        ;

        $collection = new ExtensionCollection;
        $collection->addExtension($extensionFirst);
        $collection->addExtension($extensionSecond);

        $result = $collection->onException($expectedException, $expectedRequest, $expectedAction);

        $this->assertNull($result);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ExtensionInterface
     */
    protected function createExtensionMock()
    {
        return $this->getMock('Payum\Extension\ExtensionInterface');
    }
}