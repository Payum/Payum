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
    public function couldBeConstructedWithEventDispatcherAsArgument()
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
            ->with(PayumEvents::PAYMENT_PRE_EXECUTE, $this->isInstanceOf('Payum\Core\Bridge\Symfony\Event\RequestEvent'))
        ;

        $extension = new EventDispatcherExtension($dispatcherMock);

        $extension->onPreExecute($this->createRequestMock());
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
            ->with(PayumEvents::PAYMENT_EXECUTE, $this->isInstanceOf('Payum\Core\Bridge\Symfony\Event\RequestEvent'))
        ;

        $extension = new EventDispatcherExtension($dispatcherMock);

        $extension->onExecute($this->createRequestMock(), $this->createActionMock());
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
            ->with(PayumEvents::PAYMENT_POST_EXECUTE, $this->isInstanceOf('Payum\Core\Bridge\Symfony\Event\RequestEvent'))
        ;

        $extension = new EventDispatcherExtension($dispatcherMock);

        $extension->onPostExecute($this->createRequestMock(), $this->createActionMock());
    }

    /**
     * @test
     */
    public function shouldTriggerEventWhenCallOnReply()
    {
        $dispatcherMock = $this->createEventDispatcherMock();
        $dispatcherMock
            ->expects($this->once())
            ->method('dispatch')
            ->with(PayumEvents::PAYMENT_REPLY, $this->isInstanceOf('Payum\Core\Bridge\Symfony\Event\ReplyEvent'))
        ;

        $extension = new EventDispatcherExtension($dispatcherMock);

        $extension->onReply($this->createReplyMock(), new \stdClass(), $this->createActionMock());
    }

    /**
     * @test
     */
    public function shouldTriggerEventWhenCallOnException()
    {
        $dispatcherMock = $this->createEventDispatcherMock();
        $dispatcherMock
            ->expects($this->once())
            ->method('dispatch')
            ->with(PayumEvents::PAYMENT_EXCEPTION, $this->isInstanceOf('Payum\Core\Bridge\Symfony\Event\ExceptionEvent'))
        ;

        $extension = new EventDispatcherExtension($dispatcherMock);

        $extension->onException(new \Exception(), new \stdClass());
    }

    protected function createEventDispatcherMock()
    {
        return $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    protected function createRequestMock()
    {
        return $this->getMock('Payum\Core\Request\Generic', array(), array(), '', false);
    }

    protected function createActionMock()
    {
        return $this->getMock('Payum\Core\Action\ActionInterface');
    }

    protected function createReplyMock()
    {
        return $this->getMock('Payum\Core\Reply\ReplyInterface');
    }
}
