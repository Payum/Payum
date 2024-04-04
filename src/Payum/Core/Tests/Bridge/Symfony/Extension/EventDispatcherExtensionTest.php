<?php
namespace Payum\Core\Tests\Bridge\Symfony\Extension;

use Payum\Core\Bridge\Symfony\Extension\EventDispatcherExtension;
use Payum\Core\Bridge\Symfony\PayumEvents;
use PHPUnit\Framework\TestCase;

class EventDispatcherExtensionTest extends TestCase
{
    public function testShouldImplementExtensionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Symfony\Extension\EventDispatcherExtension');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Extension\ExtensionInterface'));
    }

    public function testShouldTriggerEventWhenCallOnPreExecute()
    {
        $dispatcherMock = $this->createEventDispatcherMock();
        $dispatcherMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf('Payum\Core\Bridge\Symfony\Event\ExecuteEvent'), PayumEvents::GATEWAY_PRE_EXECUTE)
        ;

        $extension = new EventDispatcherExtension($dispatcherMock);

        $extension->onPreExecute($this->createContextMock());
    }

    public function testShouldTriggerEventWhenCallOnExecute()
    {
        $dispatcherMock = $this->createEventDispatcherMock();
        $dispatcherMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf('Payum\Core\Bridge\Symfony\Event\ExecuteEvent'), PayumEvents::GATEWAY_EXECUTE)
        ;

        $extension = new EventDispatcherExtension($dispatcherMock);

        $extension->onExecute($this->createContextMock());
    }

    public function testShouldTriggerEventWhenCallOnPostExecute()
    {
        $dispatcherMock = $this->createEventDispatcherMock();
        $dispatcherMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf('Payum\Core\Bridge\Symfony\Event\ExecuteEvent'), PayumEvents::GATEWAY_POST_EXECUTE)
        ;

        $extension = new EventDispatcherExtension($dispatcherMock);

        $extension->onPostExecute($this->createContextMock());
    }

    protected function createEventDispatcherMock()
    {
        return $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    protected function createContextMock()
    {
        return $this->createMock('Payum\Core\Extension\Context');
    }
}
