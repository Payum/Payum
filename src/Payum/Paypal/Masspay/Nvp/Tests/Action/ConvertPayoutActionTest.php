<?php
namespace Payum\Paypal\Masspay\Nvp\Tests\Action\Api;

use Payum\Core\GatewayInterface;
use Payum\Core\Model\PayoutInterface;
use Payum\Core\Model\Payout;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Tests\GenericActionTest;
use Payum\Core\Request\Convert;
use Payum\Paypal\Masspay\Nvp\Action\ConvertPayoutAction;

class ConvertPayoutActionTest extends GenericActionTest
{
    protected $actionClass = ConvertPayoutAction::class;

    protected $requestClass = Convert::class;

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass(new Payout(), 'array')),
            array(new $this->requestClass($this->getMock(PayoutInterface::class), 'array')),
            array(new $this->requestClass(new Payout(), 'array', $this->getMock(TokenInterface::class))),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array($this->getMockForAbstractClass(Generic::class, [[]])),
            array(new $this->requestClass(new \stdClass(), 'array')),
            array(new $this->requestClass(new Payout(), 'foobar')),
            array(new $this->requestClass($this->getMock(PayoutInterface::class), 'foobar')),
        );
    }

    /**
     * @test
     */
    public function shouldCorrectlyConvertPayoutToDetails()
    {
        $gatewayMock = $this->getMock(GatewayInterface::class);
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetCurrency::class))
            ->willReturnCallback(function (GetCurrency $request) {
                $request->name = 'US Dollar';
                $request->alpha3 = 'USD';
                $request->numeric = 123;
                $request->exp = 2;
                $request->country = 'US';
            })
        ;

        $payoutModel = new Payout();
        $payoutModel->setRecipientId('theRecipientId');
        $payoutModel->setCurrencyCode('USD');
        $payoutModel->setTotalAmount(123);
        $payoutModel->setDescription('the description');

        $action = new ConvertPayoutAction();
        $action->setGateway($gatewayMock);

        $action->execute($convert = new Convert($payoutModel, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertEquals([
            'CURRENCYCODE' => 'USD',
            'L_AMT0' => 1.23,
            'L_NOTE0' => 'the description',
            'RECEIVERTYPE' => 'UserID',
            'L_RECEIVERID0' => 'theRecipientId',
        ], $details);
    }

    /**
     * @test
     */
    public function shouldNotOverwriteAlreadySetExtraDetails()
    {
        $gatewayMock = $this->getMock(GatewayInterface::class);
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetCurrency::class))
            ->willReturnCallback(function (GetCurrency $request) {
                $request->name = 'US Dollar';
                $request->alpha3 = 'USD';
                $request->numeric = 123;
                $request->exp = 2;
                $request->country = 'US';
            })
        ;

        $payoutModel = new Payout();
        $payoutModel->setRecipientEmail('theRecipientEmail');
        $payoutModel->setCurrencyCode('USD');
        $payoutModel->setTotalAmount(123);
        $payoutModel->setDescription('the description');
        $payoutModel->setDetails([
            'foo' => 'fooVal',
        ]);

        $action = new ConvertPayoutAction();
        $action->setGateway($gatewayMock);

        $action->execute($convert = new Convert($payoutModel, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertEquals([
            'CURRENCYCODE' => 'USD',
            'L_AMT0' => 1.23,
            'L_NOTE0' => 'the description',
            'RECEIVERTYPE' => 'EmailAddress',
            'L_EMAIL0' => 'theRecipientEmail',
            'foo' => 'fooVal',
        ], $details);
    }
}
