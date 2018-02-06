<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\TransactionSearchAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\TransactionSearch;

class TransactionSearchActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(TransactionSearchAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(TransactionSearchAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArgument()
    {
        new TransactionSearchAction();
    }

    /**
     * @test
     */
    public function shouldSupportAuthorizeTokenRequestWithArrayAccessAsModel()
    {
        $action = new TransactionSearchAction();

        $this->assertTrue($action->supports(new TransactionSearch($this->createMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotAuthorizeTokenRequest()
    {
        $action = new TransactionSearchAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The STARTDATE fields are required.
     */
    public function throwIfRequiredFieldMissing()
    {
        $action = new TransactionSearchAction();

        $action->execute(new TransactionSearch(array()));
    }

    /**
     * @test
     */
    public function shouldCallApiTransactionSearchWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();

        $apiMock
            ->expects($this->once())
            ->method('transactionSearch')
            ->will($this->returnCallback(
                function (array $fields) use ($testCase) {
                    $testCase->assertArrayHasKey('STARTDATE', $fields);
                    $testCase->assertEquals('theStartDate', $fields['STARTDATE']);

                    $testCase->assertArrayHasKey('ENDDATE', $fields);
                    $testCase->assertEquals('theEndDate', $fields['ENDDATE']);

                    $testCase->assertArrayHasKey('EMAIL', $fields);
                    $testCase->assertEquals('theEmail', $fields['EMAIL']);

                    $testCase->assertArrayHasKey('RECEIPTID', $fields);
                    $testCase->assertEquals('theReceiptId', $fields['RECEIPTID']);

                    $testCase->assertArrayHasKey('TRANSACTIONID', $fields);
                    $testCase->assertEquals('theTransactionId', $fields['TRANSACTIONID']);

                    $testCase->assertArrayHasKey('INVNUM', $fields);
                    $testCase->assertEquals('theInvNum', $fields['INVNUM']);

                    $testCase->assertArrayHasKey('ACCT', $fields);
                    $testCase->assertEquals('theAcct', $fields['ACCT']);

                    $testCase->assertArrayHasKey('AUCTIONITEMNUMBER', $fields);
                    $testCase->assertEquals('theAuctionItemNumber', $fields['AUCTIONITEMNUMBER']);

                    $testCase->assertArrayHasKey('TRANSACTIONCLASS', $fields);
                    $testCase->assertEquals('theTransactionClass', $fields['TRANSACTIONCLASS']);

                    $testCase->assertArrayHasKey('AMT', $fields);
                    $testCase->assertEquals('theAmt', $fields['AMT']);

                    $testCase->assertArrayHasKey('CURRENCYCODE', $fields);
                    $testCase->assertEquals('theCurrencyCode', $fields['CURRENCYCODE']);

                    $testCase->assertArrayHasKey('STATUS', $fields);
                    $testCase->assertEquals('theStatus', $fields['STATUS']);

                    $testCase->assertArrayHasKey('PROFILEID', $fields);
                    $testCase->assertEquals('theProfileId', $fields['PROFILEID']);

                    return array();
                }));

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

    /**
     * @test
     */
    public function shouldCallApiTransactionSearchMethodAndUpdateModelFromResponse()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('transactionSearch')
            ->will($this->returnCallback(function () {
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
            }))
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
