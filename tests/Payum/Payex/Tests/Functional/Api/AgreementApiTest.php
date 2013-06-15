<?php
namespace Payum\Payex\Tests\Functional\Api;

use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Api\SoapClientFactory;

class AgreementApiTest extends \PHPUnit_Framework_TestCase 
{
    /**
     * @var AgreementApi
     */
    protected $agreementApi;

    public static function setUpBeforeClass()
    {
        if (empty($GLOBALS['__PAYUM_PAYEX_ACCOUNT_NUMBER'])) {
            throw new \PHPUnit_Framework_SkippedTestError('Please configure __PAYUM_PAYEX_ACCOUNT_NUMBER in your phpunit.xml');
        }
        if (empty($GLOBALS['__PAYUM_PAYEX_ENCRYPTION_KEY'])) {
            throw new \PHPUnit_Framework_SkippedTestError('Please configure __PAYUM_PAYEX_ENCRYPTION_KEY in your phpunit.xml');
        }
    }

    public function setUp()
    {
        $this->agreementApi = new AgreementApi(
            new SoapClientFactory,
            array(
                'encryptionKey' => $GLOBALS['__PAYUM_PAYEX_ENCRYPTION_KEY'],
                'accountNumber' => $GLOBALS['__PAYUM_PAYEX_ACCOUNT_NUMBER'],
                'sandbox' => true
            )
        );
    }

    /**
     * @test
     *
     * @expectedException \SoapFault
     * @expectedExceptionMessage SOAP-ERROR: Encoding: object has no 'maxAmount' property
     */
    public function throwIfTryCreateAgreementWithoutMaxAmount()
    {
        $this->agreementApi->create(array());
    }

    /**
     * @test
     */
    public function shouldFailedCreateAgreementIfRequiredParametersMissing()
    {
        $result = $this->agreementApi->create(array(
            'maxAmount' => 1000,
        ));

        $this->assertInternalType('array', $result);
        $this->assertArrayNotHasKey('orderRef', $result);
        $this->assertArrayNotHasKey('sessionRef', $result);
        $this->assertArrayNotHasKey('redirectUrl', $result);

        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('errorCode', $result);
        $this->assertNotEmpty($result['errorCode']);
        $this->assertNotEquals('OK', $result['errorCode']);

        $this->assertArrayHasKey('errorDescription', $result);
        $this->assertNotEmpty($result['errorDescription']);
        $this->assertNotEquals('OK', $result['errorDescription']);

        $this->assertArrayHasKey('errorCode', $result);
        $this->assertNotEmpty($result['errorCode']);
        $this->assertNotEquals('OK', $result['errorCode']);
    }

    /**
     * @test
     */
    public function shouldSuccessfullyCreateAgreementIfAllRequiredParametersSet()
    {
        $result = $this->agreementApi->create(array(
            'maxAmount' => 10000,
            'merchantRef' => 'aRef',
            'description' => 'aDesc',
            'startDate' => '',
            'stopDate' => ''
        ));

        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('agreementRef', $result);
        $this->assertNotEmpty($result['agreementRef']);

        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('errorCode', $result);
        $this->assertSame('OK', $result['errorCode']);

        $this->assertArrayHasKey('errorDescription', $result);
        $this->assertSame('OK', $result['errorDescription']);

        $this->assertArrayHasKey('errorCode', $result);
        $this->assertSame('OK', $result['errorCode']);
    }

    /**
     * @test
     */
    public function shouldSuccessfullyCheckNewAgreement()
    {
        $createResult = $this->agreementApi->create(array(
            'maxAmount' => 10000,
            'merchantRef' => 'aRef',
            'description' => 'aDesc',
            'startDate' => '',
            'stopDate' => ''
        ));

        //guard
        $this->assertInternalType('array', $createResult);
        $this->assertArrayHasKey('agreementRef', $createResult);
        
        $result = $this->agreementApi->check(array(
            'agreementRef' => $createResult['agreementRef'],
        ));

        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('agreementStatus', $result);
        $this->assertEquals(AgreementApi::AGREEMENTSTATUS_NOTVERIFIED, $result['agreementStatus']);

        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('errorCode', $result);
        $this->assertSame('OK', $result['errorCode']);

        $this->assertArrayHasKey('errorDescription', $result);
        $this->assertSame('OK', $result['errorDescription']);

        $this->assertArrayHasKey('errorCode', $result);
        $this->assertSame('OK', $result['errorCode']);
    }

    /**
     * @test
     */
    public function shouldAllowDeleteAgreement()
    {
        $createResult = $this->agreementApi->create(array(
            'maxAmount' => 10000,
            'merchantRef' => 'aRef',
            'description' => 'aDesc',
            'startDate' => '',
            'stopDate' => ''
        ));

        //guard
        $this->assertInternalType('array', $createResult);
        $this->assertArrayHasKey('agreementRef', $createResult);

        $result = $this->agreementApi->delete(array(
            'agreementRef' => $createResult['agreementRef'],
        ));

        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('errorCode', $result);
        $this->assertSame('OK', $result['errorCode']);

        $this->assertArrayHasKey('errorDescription', $result);
        $this->assertSame('OK', $result['errorDescription']);

        $this->assertArrayHasKey('errorCode', $result);
        $this->assertSame('OK', $result['errorCode']);
    }

    /**
     * @test
     */
    public function shouldSuccessfullyCheckDeletedAgreement()
    {
        $createResult = $this->agreementApi->create(array(
            'maxAmount' => 10000,
            'merchantRef' => 'aRef',
            'description' => 'aDesc',
            'startDate' => '',
            'stopDate' => ''
        ));

        //guard
        $this->assertInternalType('array', $createResult);
        $this->assertArrayHasKey('agreementRef', $createResult);

        $deleteResult = $this->agreementApi->delete(array(
            'agreementRef' => $createResult['agreementRef'],
        ));

        //guard
        $this->assertInternalType('array', $deleteResult);
        $this->assertArrayHasKey('errorCode', $deleteResult);
        $this->assertSame('OK', $deleteResult['errorCode']);

        $result = $this->agreementApi->check(array(
            'agreementRef' => $createResult['agreementRef'],
        ));

        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('agreementStatus', $result);
        $this->assertEquals(AgreementApi::AGREEMENTSTATUS_DELETED, $result['agreementStatus']);

        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('errorCode', $result);
        $this->assertSame('OK', $result['errorCode']);

        $this->assertArrayHasKey('errorDescription', $result);
        $this->assertSame('OK', $result['errorDescription']);

        $this->assertArrayHasKey('errorCode', $result);
        $this->assertSame('OK', $result['errorCode']);
    }

    /**
     * @test
     */
    public function shouldFailToAutoPayNotVerifiedAgreement()
    {
        $createResult = $this->agreementApi->create(array(
            'maxAmount' => 10000,
            'merchantRef' => 'aRef',
            'description' => 'aDesc',
            'startDate' => '',
            'stopDate' => ''
        ));

        //guard
        $this->assertInternalType('array', $createResult);
        $this->assertArrayHasKey('agreementRef', $createResult);

        $autoPayResult = $this->agreementApi->autoPay(array(
            'agreementRef' => $createResult['agreementRef'],
            'price' => 1000,
            'productNumber' => 'aNum',
            'description' => 'aDesc',
            'orderId' => 'anId',
            'purchaseOperation' => AgreementApi::PURCHASEOPERATION_SALE,
            'currency' => 'NOK'
        ));

        //guard
        $this->assertInternalType('array', $autoPayResult);
        $this->assertArrayHasKey('errorCode', $autoPayResult);
        $this->assertSame('AgreementNotVerified', $autoPayResult['errorCode']);
    }
}