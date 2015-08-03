<?php
namespace Payum\Core\Tests;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Generic;

abstract class BaseConvertPaymentActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Generic
     */
    protected $requestClass = 'Payum\Core\Request\Convert';

    /**
     * @var ActionInterface
     */
    protected $actionClass;

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass(new Payment())),
            array(new $this->requestClass($this->getMock(PaymentInterface::class))),
            array(new $this->requestClass(new Payment(), $this->getMock('Payum\Core\Security\TokenInterface'))),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array()))),
        );
    }

    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass($this->actionClass);

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new $this->actionClass();
    }

    /**
     * @test
     *
     * @dataProvider provideSupportedRequests
     */
    public function shouldSupportRequest($request)
    {
        $action = new $this->actionClass();

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     *
     * @dataProvider provideNotSupportedRequests
     */
    public function shouldNotSupportRequest($request)
    {
        $action = new $this->actionClass();

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     *
     * @dataProvider provideNotSupportedRequests
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute($request)
    {
        $action = new $this->actionClass();

        $action->execute($request);
    }
}
