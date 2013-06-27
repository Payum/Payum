<?php
namespace Payum\Payex\Tests\Functional\Api;

use Payum\Payex\Api\RecurringApi;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Api\SoapClientFactory;

class RecurringApiTest extends \PHPUnit_Framework_TestCase 
{
    /**
     * @var RecurringApi
     */
    protected $recurringApi;

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
        $this->recurringApi = new RecurringApi(
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
     * @expectedExceptionMessage SOAP-ERROR: Encoding: object has no 'periodType' property
     */
    public function throwIfTryStartRecurringPaymentWithoutPeriodTypeSet()
    {
        $this->recurringApi->start(array());
    }

    /**
     * @test
     *
     * @expectedException \SoapFault
     * @expectedExceptionMessage SOAP-ERROR: Encoding: object has no 'period' property
     */
    public function throwIfTryStartRecurringPaymentWithoutPeriodSet()
    {
        $this->recurringApi->start(array(
            'periodType' => RecurringApi::PERIODTYPE_HOURS,
        ));
    }
    
    /**
     * @test
     *
     * @expectedException \SoapFault
     * @expectedExceptionMessage SOAP-ERROR: Encoding: object has no 'alertPeriod' property
     */
    public function throwIfTryStartRecurringPaymentWithoutAlertSet()
    {
        $this->recurringApi->start(array(
            'periodType' => RecurringApi::PERIODTYPE_HOURS,
            'period' => 2,
        ));
    }

    /**
     * @test
     *
     * @expectedException \SoapFault
     * @expectedExceptionMessage SOAP-ERROR: Encoding: object has no 'price' property
     */
    public function throwIfTryStartRecurringPaymentWithoutPriceSet()
    {
        $this->recurringApi->start(array(
            'periodType' => RecurringApi::PERIODTYPE_HOURS,
            'period' => 2,
            'alertPeriod' => 0
        ));
    }

    /**
     * @test
     */
    public function shouldFailedStartRecurringPaymentIfRequiredParametersMissing()
    {
        $result = $this->recurringApi->start(array(
            'periodType' => RecurringApi::PERIODTYPE_HOURS,
            'period' => 2,
            'alertPeriod' => 0,
            'price' => 1000,
        ));

        $this->assertInternalType('array', $result);
        $this->assertArrayNotHasKey('recurringRef', $result);

        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('errorCode', $result);
        $this->assertNotEmpty($result['errorCode']);
        $this->assertNotEquals(RecurringApi::ERRORCODE_OK, $result['errorCode']);

        $this->assertArrayHasKey('errorDescription', $result);
        $this->assertNotEmpty($result['errorDescription']);
        $this->assertNotEquals(RecurringApi::ERRORCODE_OK, $result['errorDescription']);
        $this->assertStringStartsWith(
            'Invalid parameter:agreementRef, value:null',
            $result['errorDescription']
        );
        $this->assertArrayHasKey('errorCode', $result);
        $this->assertNotEmpty($result['errorCode']);
        $this->assertNotEquals(RecurringApi::ERRORCODE_OK, $result['errorCode']);
    }

    /**
     * @test
     */
    public function shouldFailToStartRecurringPaymentWithInvalidAgreementReg()
    {
        $result = $this->recurringApi->start(array(
            'periodType' => RecurringApi::PERIODTYPE_HOURS,
            'period' => 2,
            'alertPeriod' => 0,
            'price' => 1000,
            'agreementRef' => 'aRef',
        ));

        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('errorCode', $result);
        $this->assertNotEquals(RecurringApi::ERRORCODE_OK, $result['errorCode']);
        
        $this->assertArrayHasKey('errorDescription', $result);
        $this->assertStringStartsWith('Invalid parameter:agreementRef', $result['errorDescription']);
    }
}