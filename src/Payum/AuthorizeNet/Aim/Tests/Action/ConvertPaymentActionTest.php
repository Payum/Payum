<?php
namespace Payum\AuthorizeNet\Aim\Tests\Action\Api;

use Payum\AuthorizeNet\Aim\Action\ConvertPaymentAction;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Tests\GenericActionTest;
use Payum\Core\Tests\SkipOnPhp7Trait;

class ConvertPaymentActionTest extends GenericActionTest
{
    use SkipOnPhp7Trait;

    protected $actionClass = 'Payum\AuthorizeNet\Aim\Action\ConvertPaymentAction';

    protected $requestClass = 'Payum\Core\Request\Convert';

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass(new Payment(), 'array')),
            array(new $this->requestClass($this->getMock(PaymentInterface::class), 'array')),
            array(new $this->requestClass(new Payment(), 'array', $this->getMock('Payum\Core\Security\TokenInterface'))),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array()))),
            array(new $this->requestClass(new \stdClass(), 'array')),
            array(new $this->requestClass(new Payment(), 'foobar')),
            array(new $this->requestClass($this->getMock(PaymentInterface::class), 'foobar')),
        );
    }

    /**
     * @test
     */
    public function shouldCorrectlyConvertPaymentToArray()
    {
        $gatewayMock = $this->getMock('Payum\Core\GatewayInterface');
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetCurrency'))
            ->willReturnCallback(function (GetCurrency $request) {
                $request->name = 'US Dollar';
                $request->alpha3 = 'USD';
                $request->numeric = 123;
                $request->exp = 2;
                $request->country = 'US';
            })
        ;

        $payment = new Payment();
        $payment->setNumber('theNumber');
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setClientId('theClientId');
        $payment->setClientEmail('theClientEmail');

        $action = new ConvertPaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($convert = new Convert($payment, 'array'));

        $result = $convert->getResult();

        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);

        $this->assertArrayHasKey('amount', $result);
        $this->assertEquals(1.23, $result['amount']);

        $this->assertArrayHasKey('invoice_num', $result);
        $this->assertEquals('theNumber', $result['invoice_num']);

        $this->assertArrayHasKey('description', $result);
        $this->assertEquals('the description', $result['description']);

        $this->assertArrayHasKey('cust_id', $result);
        $this->assertEquals('theClientId', $result['cust_id']);

        $this->assertArrayHasKey('email', $result);
        $this->assertEquals('theClientEmail', $result['email']);
    }

    /**
     * @test
     */
    public function shouldNotOverwriteAlreadySetExtraDetails()
    {
        $gatewayMock = $this->getMock('Payum\Core\GatewayInterface');
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetCurrency'))
            ->willReturnCallback(function (GetCurrency $request) {
                $request->name = 'US Dollar';
                $request->alpha3 = 'USD';
                $request->numeric = 123;
                $request->exp = 2;
                $request->country = 'US';
            })
        ;

        $payment = new Payment();
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setDetails(array(
            'foo' => 'fooVal',
        ));

        $action = new ConvertPaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($convert = new Convert($payment, 'array'));

        $result = $convert->getResult();

        $this->assertNotEmpty($result);

        $this->assertArrayHasKey('foo', $result);
        $this->assertEquals('fooVal', $result['foo']);
    }
}
