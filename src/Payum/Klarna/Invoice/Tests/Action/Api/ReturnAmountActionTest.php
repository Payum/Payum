<?php

namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Klarna;
use KlarnaException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Tests\GenericApiAwareActionTest;
use Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction;
use Payum\Klarna\Invoice\Action\Api\ReturnAmountAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\ReturnAmount;
use PHPUnit\Framework\MockObject\MockObject;
use PhpXmlRpc\Client;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

class ReturnAmountActionTest extends GenericApiAwareActionTest
{
    public function testShouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new ReflectionClass(ReturnAmountAction::class);

        $this->assertTrue($rc->isSubclassOf(BaseApiAwareAction::class));
    }

    public function testThrowApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = new ReturnAmountAction($this->createKlarnaMock());

        $action->setApi(new stdClass());
    }

    public function testShouldSupportReserveAmountWithArrayAsModel()
    {
        $action = new ReturnAmountAction();

        $this->assertTrue($action->supports(new ReturnAmount([])));
    }

    public function testShouldNotSupportAnythingNotReserveAmount()
    {
        $action = new ReturnAmountAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportReturnAmountWithNotArrayAccessModel()
    {
        $action = new ReturnAmountAction();

        $this->assertFalse($action->supports(new ReturnAmount(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new ReturnAmountAction();

        $action->execute(new stdClass());
    }

    public function testShouldCallKlarnaReturnAmount()
    {
        $details = [
            'invoice_number' => 'invoice number',
            'amount' => 100,
            'vat' => 50,
            'flags' => 123,
            'description' => 'description',
        ];

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('returnAmount')
            ->with(
                $details['invoice_number'],
                $details['amount'],
                $details['vat'],
                $details['flags'],
                $details['description']
            )
        ;

        $action = new ReturnAmountAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute(new ReturnAmount($details));
    }

    public function testShouldCatchKlarnaExceptionAndSetErrorInfoToDetails()
    {
        $details = [
            'invoice_number' => 'invoice number',
            'amount' => 100,
            'vat' => 50,
            'flags' => 123,
            'description' => 'description',
        ];

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('returnAmount')
            ->with(
                $details['invoice_number'],
                $details['amount'],
                $details['vat'],
                $details['flags'],
                $details['description']
            )
            ->willThrowException(new KlarnaException('theMessage', 123))
        ;

        $action = new ReturnAmountAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($reserve = new ReturnAmount($details));

        $postDetails = $reserve->getModel();
        $this->assertSame(123, $postDetails['error_code']);
        $this->assertSame('theMessage', $postDetails['error_message']);
    }

    protected function getActionClass(): string
    {
        return ReturnAmountAction::class;
    }

    protected function getApiClass()
    {
        return new Config();
    }

    /**
     * @return MockObject|Klarna
     */
    protected function createKlarnaMock()
    {
        $klarnaMock = $this->createMock(Klarna::class, ['config', 'returnAmount']);

        $rp = new ReflectionProperty($klarnaMock, 'xmlrpc');
        $rp->setAccessible(true);
        $rp->setValue($klarnaMock, $this->createMock(class_exists('xmlrpc_client') ? 'xmlrpc_client' : Client::class));
        $rp->setAccessible(false);

        return $klarnaMock;
    }
}
