<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\TransactionSearchAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\TransactionSearch;

class TransactionSearchActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(TransactionSearchAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(TransactionSearchAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldSupportAuthorizeTokenRequestWithArrayAccessAsModel()
    {
        $action = new TransactionSearchAction();

        $this->assertTrue($action->supports(new TransactionSearch($this->createMock('ArrayAccess'))));
    }

    public function testShouldNotSupportAnythingNotAuthorizeTokenRequest()
    {
        $action = new TransactionSearchAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfRequiredFieldMissing()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The STARTDATE fields are required.');
        $action = new TransactionSearchAction();

        $action->execute(new TransactionSearch(array()));
    }

    public function testShouldCallApiTransactionSearchWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();

        $apiMock
            ->expects($this->once())
            ->method('transactionSearch')
            ->willReturnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('STARTDATE', $fields);
                $testCase->assertSame('theStartDate', $fields['STARTDATE']);

                $testCase->assertArrayHasKey('ENDDATE', $fields);
                $testCase->assertSame('theEndDate', $fields['ENDDATE']);

                $testCase->assertArrayHasKey('EMAIL', $fields);
                $testCase->assertSame('theEmail', $fields['EMAIL']);

                $testCase->assertArrayHasKey('RECEIPTID', $fields);
                $testCase->assertSame('theReceiptId', $fields['RECEIPTID']);

                $testCase->assertArrayHasKey('TRANSACTIONID', $fields);
                $testCase->assertSame('theTransactionId', $fields['TRANSACTIONID']);

                $testCase->assertArrayHasKey('INVNUM', $fields);
                $testCase->assertSame('theInvNum', $fields['INVNUM']);

                $testCase->assertArrayHasKey('ACCT', $fields);
                $testCase->assertSame('theAcct', $fields['ACCT']);

                $testCase->assertArrayHasKey('AUCTIONITEMNUMBER', $fields);
                $testCase->assertSame('theAuctionItemNumber', $fields['AUCTIONITEMNUMBER']);

                $testCase->assertArrayHasKey('TRANSACTIONCLASS', $fields);
                $testCase->assertSame('theTransactionClass', $fields['TRANSACTIONCLASS']);

                $testCase->assertArrayHasKey('AMT', $fields);
                $testCase->assertSame('theAmt', $fields['AMT']);

                $testCase->assertArrayHasKey('CURRENCYCODE', $fields);
                $testCase->assertSame('theCurrencyCode', $fields['CURRENCYCODE']);

                $testCase->assertArrayHasKey('STATUS', $fields);
                $testCase->assertSame('theStatus', $fields['STATUS']);

                $testCase->assertArrayHasKey('PROFILEID', $fields);
                $testCase->assertSame('theProfileId', $fields['PROFILEID']);

                return array();
            });

        $action = new TransactionSearchAction();
        $action->setApi($apiMock);

        $request = new TransactionSearch(array(
            'STARTDATE' => 'theStartDate',
            'ENDDATE' => 'theEndDate',
            'EMAIL' => 'theEmail',
            'RECEIPTID' => 'theReceiptId',
            'TRANSACTIONID' => 'theTransactionId',
            'INVNUM' => 'theInvNum',
            'ACCT' => 'theAcct',
            'AUCTIONITEMNUMBER' => 'theAuctionItemNumber',
            'TRANSACTIONCLASS' => 'theTransactionClass',
            'AMT' => 'theAmt',
            'CURRENCYCODE' => 'theCurrencyCode',
            'STATUS' => 'theStatus',
            'PROFILEID' => 'theProfileId',
        ));

        $action->execute($request);
    }

    public function testShouldCallApiTransactionSearchMethodAndUpdateModelFromResponse()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('transactionSearch')
            ->willReturnCallback(function () {
                return array(
                    'L_TIMESTAMP0' => 'theTransactionTimestamp',
                    'L_TIMEZONE0' => 'TheTimezone',
                    'L_TYPE0' => 'theTransactionType',
                    'L_EMAIL1' => 'theEmail',
                    'L_NAME0' => 'theName',
                    'L_TRANSACTIONID0' => 'theProfileId',
                    'L_STATUS0' => 'theStatus',
                    'TIMESTAMP' => 'theTimestamp',
                    'ACK' => 'TheAckStatus',
                    'VERSION' => 'theVersion',
                    'BUILD' => 'TheVersionBuild'
                );
            })
        ;

        $action = new TransactionSearchAction();
        $action->setApi($apiMock);

        $request = new TransactionSearch(array(
            'STARTDATE' => 'theStartDate',
            'PROFILEID' => 'theProfileId',
        ));

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('L_TIMESTAMP0', $model);
        $this->assertArrayHasKey('L_TIMEZONE0', $model);
        $this->assertArrayHasKey('L_TYPE0', $model);
        $this->assertArrayHasKey('L_EMAIL1', $model);
        $this->assertArrayHasKey('L_NAME0', $model);
        $this->assertArrayHasKey('L_TRANSACTIONID0', $model);
        $this->assertArrayHasKey('L_STATUS0', $model);
        $this->assertArrayHasKey('TIMESTAMP', $model);
        $this->assertArrayHasKey('ACK', $model);
        $this->assertArrayHasKey('VERSION', $model);
        $this->assertArrayHasKey('BUILD', $model);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
