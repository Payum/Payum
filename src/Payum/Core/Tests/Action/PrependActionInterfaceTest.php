<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\PrependActionInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PrependActionInterfaceTest extends TestCase
{
    public function testShouldBeInterface(): void
    {
        $rc = new ReflectionClass(PrependActionInterface::class);

        $this->assertTrue($rc->isInterface());
    }

    public function testShouldBeMarkerInterfaceWithoutAnyMethod(): void
    {
        $rc = new ReflectionClass(PrependActionInterface::class);

        $this->assertSame([], $rc->getMethods());
    }

    public function testShouldNotExtendActionInterface(): void
    {
        $rc = new ReflectionClass(PrependActionInterface::class);

        $this->assertFalse($rc->isSubclassOf(ActionInterface::class));
        $this->assertSame([], $rc->getInterfaceNames());
    }

    public function testShouldBeImplementableTogetherWithActionInterface(): void
    {
        $action = new class() implements ActionInterface, PrependActionInterface {
            public function execute($request): void
            {
            }

            public function supports($request): bool
            {
                return true;
            }
        };

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertInstanceOf(PrependActionInterface::class, $action);
    }

    public function testShouldNotMarkPlainActionAsPrepending(): void
    {
        $action = new class() implements ActionInterface {
            public function execute($request): void
            {
            }

            public function supports($request): bool
            {
                return true;
            }
        };

        $this->assertNotInstanceOf(PrependActionInterface::class, $action);
    }
}
