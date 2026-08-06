<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Extension;

use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Extension\PrependExtensionInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PrependExtensionInterfaceTest extends TestCase
{
    public function testShouldBeInterface(): void
    {
        $rc = new ReflectionClass(PrependExtensionInterface::class);

        $this->assertTrue($rc->isInterface());
    }

    public function testShouldBeMarkerInterfaceWithoutAnyMethod(): void
    {
        $rc = new ReflectionClass(PrependExtensionInterface::class);

        $this->assertSame([], $rc->getMethods());
    }

    public function testShouldNotExtendExtensionInterface(): void
    {
        $rc = new ReflectionClass(PrependExtensionInterface::class);

        $this->assertFalse($rc->isSubclassOf(ExtensionInterface::class));
        $this->assertSame([], $rc->getInterfaceNames());
    }

    public function testShouldBeImplementableTogetherWithExtensionInterface(): void
    {
        $extension = new class() implements ExtensionInterface, PrependExtensionInterface {
            public function onPreExecute(Context $context): void
            {
            }

            public function onExecute(Context $context): void
            {
            }

            public function onPostExecute(Context $context): void
            {
            }
        };

        $this->assertInstanceOf(ExtensionInterface::class, $extension);
        $this->assertInstanceOf(PrependExtensionInterface::class, $extension);
    }

    public function testShouldNotMarkPlainExtensionAsPrepending(): void
    {
        $extension = $this->createMock(ExtensionInterface::class);

        $this->assertNotInstanceOf(PrependExtensionInterface::class, $extension);
    }
}
