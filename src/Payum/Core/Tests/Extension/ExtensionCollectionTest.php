<?php
namespace Payum\Core\Tests\Extension;

use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionCollection;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Tests\TestCase;

class ExtensionCollectionTest extends TestCase
{
    public function testShouldImplementExtensionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Extension\ExtensionCollection');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Extension\ExtensionInterface'));
    }

    public function testShouldAllowAddExtensionAppendByDefault()
    {
        $extensionFirst = $this->createExtensionMock();
        $extensionSecond = $this->createExtensionMock();

        $collection = new ExtensionCollection();

        $collection->addExtension($extensionFirst);
        $collection->addExtension($extensionSecond);

        $addedExtensions = $this->readAttribute($collection, 'extensions');

        $this->assertIsArray($addedExtensions);
        $this->assertCount(2, $addedExtensions);

        $this->assertSame($extensionFirst, $addedExtensions[0]);
        $this->assertSame($extensionSecond, $addedExtensions[1]);
    }

    public function testShouldAllowAddExtensionWithForcedPrepend()
    {
        $extensionFirst = $this->createExtensionMock();
        $extensionSecond = $this->createExtensionMock();

        $collection = new ExtensionCollection();

        $collection->addExtension($extensionFirst);
        $collection->addExtension($extensionSecond, $forcePrepend = true);

        $addedExtensions = $this->readAttribute($collection, 'extensions');

        $this->assertIsArray($addedExtensions);
        $this->assertCount(2, $addedExtensions);

        $this->assertSame($extensionSecond, $addedExtensions[0]);
        $this->assertSame($extensionFirst, $addedExtensions[1]);
    }

    public function testShouldCallOnPreExecuteForAllExtensionsInCollection()
    {
        $expectedContext = $this->createContextMock();

        $extensionFirst = $this->createExtensionMock();
        $extensionFirst
            ->expects($this->once())
            ->method('onPreExecute')
            ->with($this->identicalTo($expectedContext))
        ;

        $extensionSecond = $this->createExtensionMock();
        $extensionSecond
            ->expects($this->once())
            ->method('onPreExecute')
            ->with($this->identicalTo($expectedContext))
        ;

        $collection = new ExtensionCollection();
        $collection->addExtension($extensionFirst);
        $collection->addExtension($extensionSecond);

        $result = $collection->onPreExecute($expectedContext);

        $this->assertNull($result);
    }

    public function testShouldCallOnExecuteForAllExtensionsInCollection()
    {
        $expectedContext = $this->createContextMock();

        $extensionFirst = $this->createExtensionMock();
        $extensionFirst
            ->expects($this->once())
            ->method('onExecute')
            ->with($this->identicalTo($expectedContext))
        ;

        $extensionSecond = $this->createExtensionMock();
        $extensionSecond
            ->expects($this->once())
            ->method('onExecute')
            ->with($expectedContext)
        ;

        $collection = new ExtensionCollection();
        $collection->addExtension($extensionFirst);
        $collection->addExtension($extensionSecond);

        $result = $collection->onExecute($expectedContext);

        $this->assertNull($result);
    }

    public function testShouldCallOnPostExecuteForAllExtensionsInCollection()
    {
        $expectedContext = $this->createContextMock();

        $extensionFirst = $this->createExtensionMock();
        $extensionFirst
            ->expects($this->once())
            ->method('onPostExecute')
            ->with($this->identicalTo($expectedContext))
        ;

        $extensionSecond = $this->createExtensionMock();
        $extensionSecond
            ->expects($this->once())
            ->method('onPostExecute')
            ->with($expectedContext)
        ;

        $collection = new ExtensionCollection();
        $collection->addExtension($extensionFirst);
        $collection->addExtension($extensionSecond);

        $result = $collection->onPostExecute($expectedContext);

        $this->assertNull($result);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Context
     */
    protected function createContextMock()
    {
        return $this->createMock('Payum\Core\Extension\Context', array(), array(), '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ExtensionInterface
     */
    protected function createExtensionMock()
    {
        return $this->createMock('Payum\Core\Extension\ExtensionInterface');
    }
}
