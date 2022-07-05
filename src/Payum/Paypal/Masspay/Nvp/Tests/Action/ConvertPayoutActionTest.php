<?php

namespace Payum\Paypal\Masspay\Nvp\Tests\Action;

use Payum\Core\GatewayInterface;
use Payum\Core\Model\Payout;
use Payum\Core\Model\PayoutInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\Masspay\Nvp\Action\ConvertPayoutAction;

class ConvertPayoutActionTest extends GenericActionTest
{
    protected $actionClass = ConvertPayoutAction::class;

    protected $requestClass = Convert::class;

    public function provideSupportedRequests(): \Iterator
    {
        yield array(new $this->requestClass(new Payout(), 'array'));
        yield array(new $this->requestClass($this->createMock(PayoutInterface::class), 'array'));
        yield array(new $this->requestClass(new Payout(), 'array', $this->createMock(TokenInterface::class)));
    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield array('foo');
        yield array(array('foo'));
        yield array(new \stdClass());
        yield array($this->getMockForAbstractClass(Generic::class, [[]]));
        yield array(new $this->requestClass(new \stdClass(), 'array'));
        yield array(new $this->requestClass(new Payout(), 'foobar'));
        yield array(new $this->requestClass($this->createMock(PayoutInterface::class), 'foobar'));
    }

    /**
     * @test
     */
    public function shouldCorrectlyConvertPayoutToDetails()
    {
        $gatewayMock = $this->createMock(GatewayInterface::class);
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
        $gatewayMock = $this->createMock(GatewayInterface::class);
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
