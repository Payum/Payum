<?php
namespace Payum\Core\Tests\Extension;

use Payum\Core\Bridge\Symfony\Extension\EventDispatcherExtension;
use Payum\Core\Bridge\Symfony\PayumEvents;
use Payum\Core\Bridge\Symfony\Event\RequestEvent;
use Payum\Core\Bridge\Symfony\Event\ReplyEvent;
use Payum\Core\Bridge\Symfony\Event\ExceptionEvent;

class EventDispatcherExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementExtensionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Symfony\Extension\EventDispatcherExtension');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Extension\ExtensionInterface'));
    }

    /**
     * @test
     */
    public function shouldBeConstructedWithEventDispatcherAsArgument()
    {
        new EventDispatcherExtension($this->createEventDispatcherMock());
    }

    /**
     * @test
     */
    public function shouldTriggerEventWhenCallOnPreExecute()
    {
        $dispatcherMock = $this->createEventDispatcherMock();
        $dispatcherMock
            ->expects($this->once())
            ->method('dispatch')
            ->with(PayumEvents::GATEWAY_PRE_EXECUTE, $this->isInstanceOf('Payum\Core\Bridge\Symfony\Event\ExecuteEvent'))
        ;

        $extension = new EventDispatcherExtension($dispatcherMock);

        $extension->onPreExecute($this->createContextMock());
    }

    /**
     * @test
     */
    public function shouldTriggerEventWhenCallOnExecute()
    {
        $dispatcherMock = $this->createEventDispatcherMock();
        $dispatcherMock
            ->expects($this->once())
            ->method('dispatch')
            ->with(PayumEvents::GATEWAY_EXECUTE, $this->isInstanceOf('Payum\Core\Bridge\Symfony\Event\ExecuteEvent'))
        ;

        $extension = new EventDispatcherExtension($dispatcherMock);

        $extension->onExecute($this->createContextMock());
    }

    /**
     * @test
     */
    public function shouldTriggerEventWhenCallOnPostExecute()
    {
        $dispatcherMock = $this->createEventDispatcherMock();
        $dispatcherMock
            ->expects($this->once())
            ->method('dispatch')
            ->with(PayumEvents::GATEWAY_POST_EXECUTE, $this->isInstanceOf('Payum\Core\Bridge\Symfony\Event\ExecuteEvent'))
        ;

        $extension = new EventDispatcherExtension($dispatcherMock);

        $extension->onPostExecute($this->createContextMock());
    }

    protected function createEventDispatcherMock()
    {
        return $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    protected function createContextMock()
    {
        return $this->getMock('Payum\Core\Extension\Context', array(), array(), '', false);
    }
}
