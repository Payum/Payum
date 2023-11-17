<?php

namespace Payum\Core\Tests\Extension;

use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionCollection;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;

class ExtensionCollectionTest extends TestCase
{
    public function testShouldImplementExtensionInterface(): void
    {
        $rc = new ReflectionClass(ExtensionCollection::class);

        $this->assertTrue($rc->implementsInterface(ExtensionInterface::class));
    }

    public function testShouldAllowAddExtensionAppendByDefault(): void
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

    public function testShouldAllowAddExtensionWithForcedPrepend(): void
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

    public function testShouldCallOnPreExecuteForAllExtensionsInCollection(): void
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

        $collection->onPreExecute($expectedContext);
    }

    public function testShouldCallOnExecuteForAllExtensionsInCollection(): void
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

        $collection->onExecute($expectedContext);
    }

    public function testShouldCallOnPostExecuteForAllExtensionsInCollection(): void
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

        $collection->onPostExecute($expectedContext);
    }

    /**
     * @return MockObject|Context
     */
    protected function createContextMock()
    {
        return $this->createMock(Context::class);
    }

    /**
     * @return MockObject|ExtensionInterface
     */
    protected function createExtensionMock()
    {
        return $this->createMock(ExtensionInterface::class);
    }
}
