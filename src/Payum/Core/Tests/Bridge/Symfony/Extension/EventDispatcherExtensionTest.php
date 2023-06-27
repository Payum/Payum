<?php

namespace Payum\Core\Tests\Bridge\Symfony\Extension;

use Payum\Core\Bridge\Symfony\Event\ExecuteEvent;
use Payum\Core\Bridge\Symfony\Extension\EventDispatcherExtension;
use Payum\Core\Bridge\Symfony\PayumEvents;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventDispatcherExtensionTest extends TestCase
{
    public function testShouldImplementExtensionInterface(): void
    {
        $rc = new ReflectionClass(EventDispatcherExtension::class);

        $this->assertTrue($rc->implementsInterface(ExtensionInterface::class));
    }

    public function testShouldTriggerEventWhenCallOnPreExecute(): void
    {
        $dispatcherMock = $this->createEventDispatcherMock();
        $dispatcherMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ExecuteEvent::class), PayumEvents::GATEWAY_PRE_EXECUTE)
        ;

        $extension = new EventDispatcherExtension($dispatcherMock);

        $extension->onPreExecute($this->createContextMock());
    }

    public function testShouldTriggerEventWhenCallOnExecute(): void
    {
        $dispatcherMock = $this->createEventDispatcherMock();
        $dispatcherMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ExecuteEvent::class), PayumEvents::GATEWAY_EXECUTE)
        ;

        $extension = new EventDispatcherExtension($dispatcherMock);

        $extension->onExecute($this->createContextMock());
    }

    public function testShouldTriggerEventWhenCallOnPostExecute(): void
    {
        $dispatcherMock = $this->createEventDispatcherMock();
        $dispatcherMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ExecuteEvent::class), PayumEvents::GATEWAY_POST_EXECUTE)
        ;

        $extension = new EventDispatcherExtension($dispatcherMock);

        $extension->onPostExecute($this->createContextMock());
    }

    protected function createEventDispatcherMock()
    {
        return $this->createMock(EventDispatcherInterface::class);
    }

    protected function createContextMock()
    {
        return $this->createMock(Context::class);
    }
}
