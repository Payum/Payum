<?php
namespace Payum\Core\Tests;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;

abstract class BaseConvertPaymentActionTest extends TestCase
{
    /**
     * @var Generic
     */
    protected $requestClass = 'Payum\Core\Request\Convert';

    /**
     * @var ActionInterface
     */
    protected $actionClass;

    public function provideSupportedRequests(): \Iterator
    {
        yield array(new $this->requestClass(new Payment()));
        yield array(new $this->requestClass($this->createMock(PaymentInterface::class)));
        yield array(new $this->requestClass(new Payment(), $this->createMock('Payum\Core\Security\TokenInterface')));
    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield array('foo');
        yield array(array('foo'));
        yield array(new \stdClass());
        yield array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array())));
    }

    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass($this->actionClass);

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @dataProvider provideSupportedRequests
     */
    public function testShouldSupportRequest($request)
    {
        $action = new $this->actionClass();

        $this->assertTrue($action->supports($request));
    }

    /**
     * @dataProvider provideNotSupportedRequests
     */
    public function testShouldNotSupportRequest($request)
    {
        $action = new $this->actionClass();

        $this->assertFalse($action->supports($request));
    }

    /**
     * @dataProvider provideNotSupportedRequests
     */
    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute($request)
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new $this->actionClass();

        $action->execute($request);
    }
}
