<?php
namespace Payum\Payex\Tests\Functional\Api;

use Payum\Payex\Api\PxOrder;
use Payum\Payex\Api\SoapClientFactory;

class PxOrderTest extends \PHPUnit_Framework_TestCase 
{
    /**
     * @var PxOrder
     */
    protected $pxOrder;
    
    public static function setUpBeforeClass()
    {
        if (false == isset($GLOBALS['__PAYUM_PAYEX_ACCOUNT_NUMBER'])) {
            throw new \PHPUnit_Framework_SkippedTestError('Please configure __PAYUM_PAYEX_ACCOUNT_NUMBER in your phpunit.xml');
        }
        if (false == isset($GLOBALS['__PAYUM_PAYEX_ENCRYPTION_KEY'])) {
            throw new \PHPUnit_Framework_SkippedTestError('Please configure __PAYUM_PAYEX_ENCRYPTION_KEY in your phpunit.xml');
        }
    }
    
    public function setUp()
    {
        $this->pxOrder = new PxOrder(
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
     * @expectedExceptionMessage SOAP-ERROR: Encoding: object has no 'price' property
     */
    public function throwIfTryInitializeWithoutPrice()
    {
        $this->pxOrder->Initialize8(array());
    }


    /**
     * @test
     *
     * @expectedException \SoapFault
     * @expectedExceptionMessage SOAP-ERROR: Encoding: object has no 'vat' property
     */
    public function throwIfTryInitializeWithoutVat()
    {
        $this->pxOrder->Initialize8(array(
            'price' => 1000,
        ));
    }

    /**
     * @test
     */
    public function shouldFailedInitializeIfRequiredParametersMissing()
    {
        $result = $this->pxOrder->Initialize8(array(
            'price' => 1000,
            'priceArgList' => '',
            'vat' => 0,
            'currency' => 'NOK',
        ));

        $this->assertInstanceOf('stdClass', $result);
        $this->assertObjectNotHasAttribute('orderRef', $result);
        $this->assertObjectNotHasAttribute('sessionRef', $result);
        $this->assertObjectNotHasAttribute('redirectUrl', $result);
        
        $this->assertInstanceOf('stdClass', $result->status);

        $this->assertObjectHasAttribute('code', $result->status);
        $this->assertNotEmpty($result->status->code);
        $this->assertNotEquals('OK', $result->status->code);

        $this->assertObjectHasAttribute('description', $result->status);
        $this->assertNotEmpty($result->status->description);
        $this->assertNotEquals('OK', $result->status->description);

        $this->assertObjectHasAttribute('errorCode', $result->status);
        $this->assertNotEmpty($result->status->errorCode);
        $this->assertNotEquals('OK', $result->status->errorCode);
    }
    
    /**
     * @test
     */
    public function shouldSuccessfullyInitializeIfAllRequiredParametersSet()
    {
        $result = $this->pxOrder->Initialize8(array(
            'price' => 1000,
            'priceArgList' => '',
            'vat' => 0,
            'currency' => 'NOK',
            'orderID' => 123,
            'productNumber' => 123,
            'purchaseOperation' => 'AUTHORIZATION',
            'view' => 'CC',
            'description' => 'a description',
            'additionalValues' => '',
            'returnUrl' => 'http://example.com/a_return_url',
            'cancelUrl' => 'http://example.com/a_cancel_url',
            'externalID' => '',
            'clientIPAddress' => '127.0.0.1',
            'clientIdentifier' => 'USER-AGENT=cli-php',
            'agreementRef' => '',
            'clientLanguage' => 'en-US',
        ));

        $this->assertInstanceOf('stdClass', $result);
        
        $this->assertObjectHasAttribute('status', $result);
        $this->assertNotEmpty($result->status);

        $this->assertObjectHasAttribute('orderRef', $result);
        $this->assertNotEmpty($result->orderRef);

        $this->assertObjectHasAttribute('sessionRef', $result);
        $this->assertNotEmpty($result->sessionRef);

        $this->assertObjectHasAttribute('redirectUrl', $result);
        $this->assertNotEmpty($result->redirectUrl);

        $this->assertInstanceOf('stdClass', $result->status);
        
        $this->assertObjectHasAttribute('code', $result->status);
        $this->assertSame('OK', $result->status->code);

        $this->assertObjectHasAttribute('description', $result->status);
        $this->assertSame('OK', $result->status->description);

        $this->assertObjectHasAttribute('errorCode', $result->status);
        $this->assertSame('OK', $result->status->description);   
    }

    /**
     * @test
     */
    public function shouldFailedCompleteIfRequiredParametersMissing()
    {
        $result = $this->pxOrder->Complete(array());

        $this->assertInstanceOf('stdClass', $result);
        $this->assertObjectNotHasAttribute('transactionStatus', $result);
        $this->assertObjectNotHasAttribute('transactionNumber', $result);
        $this->assertObjectNotHasAttribute('orderStatus', $result);

        $this->assertInstanceOf('stdClass', $result->status);

        $this->assertObjectHasAttribute('code', $result->status);
        $this->assertNotEmpty($result->status->code);
        $this->assertNotEquals('OK', $result->status->code);

        $this->assertObjectHasAttribute('description', $result->status);
        $this->assertNotEmpty($result->status->description);
        $this->assertNotEquals('OK', $result->status->description);

        $this->assertObjectHasAttribute('errorCode', $result->status);
        $this->assertNotEmpty($result->status->errorCode);
        $this->assertNotEquals('OK', $result->status->errorCode);
    }
}