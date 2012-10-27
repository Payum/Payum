<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request;

use Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction;
use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;

class InstructionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementPayumInstructionInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction');
        
        $this->assertTrue($rc->implementsInterface('Payum\Request\InstructionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new Instruction();
    }
    
    public function provideStringFields()
    {
        return array(
            array('getToken', 'setToken', 'theValue', 'TOKEN'),
            array('getCustom', 'setCustom', 'theValue', 'CUSTOM'),
            array('getInvnum', 'setInvnum', 'theValue', 'INVNUM'),
            array('getPhonenum', 'setPhonenum', 'theValue', 'PHONENUM'),
            array('getPaypaladjustment', 'setPaypaladjustment', 'theValue', 'PAYPALADJUSTMENT'),
            array('getNote', 'setNote', 'theValue', 'NOTE'),
            array('getRedirectrequired', 'setRedirectrequired', 'theValue', 'REDIRECTREQUIRED'),
            array('getCheckoutstatus', 'setCheckoutstatus', 'theValue', 'CHECKOUTSTATUS'),
            array('getGiftmessage', 'setGiftmessage', 'theValue', 'GIFTMESSAGE'),
            array('getGiftreceiptenable', 'setGiftreceiptenable', 'theValue', 'GIFTRECEIPTENABLE'),
            array('getGiftwrapname', 'setGiftwrapname', 'theValue', 'GIFTWRAPNAME'),
            array('getGiftwrapamount', 'setGiftwrapamount', 'theValue', 'GIFTWRAPAMOUNT'),
            array('getBuyermarketingemail', 'setBuyermarketingemail', 'theValue', 'BUYERMARKETINGEMAIL'),
            array('getSurveyquestion', 'setSurveyquestion', 'theValue', 'SURVEYQUESTION'),
            array('getSurveychoiceselected', 'setSurveychoiceselected', 'theValue', 'SURVEYCHOICESELECTED'),
            array('getEmail', 'setEmail', 'theValue', 'EMAIL'),
            array('getPayerid', 'setPayerid', 'theValue', 'PAYERID'),
            array('getPayerstatus', 'setPayerstatus', 'theValue', 'PAYERSTATUS'),
            array('getCountrycode', 'setCountrycode', 'theValue', 'COUNTRYCODE'),
            array('getBusiness', 'setBusiness', 'theValue', 'BUSINESS'),
            array('getSalutation', 'setSalutation', 'theValue', 'SALUTATION'),
            array('getFirstname', 'setFirstname', 'theValue', 'FIRSTNAME'),
            array('getMiddlename', 'setMiddlename', 'theValue', 'MIDDLENAME'),
            array('getLastname', 'setLastname', 'theValue', 'LASTNAME'),
            array('getSuffix', 'setSuffix', 'theValue', 'SUFFIX'),
            array('getTimestamp', 'setTimestamp', 'theValue', 'TIMESTAMP'),
            array('getCorrelationid', 'setCorrelationid', 'theValue', 'CORRELATIONID'),
            array('getVersion', 'setVersion', 'theValue', 'VERSION'),
            array('getBuild', 'setBuild', 'theValue', 'BUILD'),
            array('getAck', 'setAck', 'theValue', 'ACK'),
        );
    }

    public function provideArrayFields()
    {
        return array(
            array('getPaymentrequestNShiptostreet', 'setPaymentrequestNShiptostreet', 'PAYMENTREQUEST_0_SHIPTOSTREET', 'PAYMENTREQUEST_9_SHIPTOSTREET'),
            array('getPaymentrequestNShiptoname', 'setPaymentrequestNShiptoname', 'PAYMENTREQUEST_0_SHIPTONAME', 'PAYMENTREQUEST_9_SHIPTONAME'),
            array('getPaymentrequestNShiptostreet2', 'setPaymentrequestNShiptostreet2', 'PAYMENTREQUEST_0_SHIPTOSTREET2', 'PAYMENTREQUEST_9_SHIPTOSTREET2'),
            array('getPaymentrequestNShiptocity', 'setPaymentrequestNShiptocity', 'PAYMENTREQUEST_0_SHIPTOCITY', 'PAYMENTREQUEST_9_SHIPTOCITY'),
            array('getPaymentrequestNShiptostate', 'setPaymentrequestNShiptostate', 'PAYMENTREQUEST_0_SHIPTOSTATE', 'PAYMENTREQUEST_9_SHIPTOSTATE'),
            array('getPaymentrequestNShiptozip', 'setPaymentrequestNShiptozip', 'PAYMENTREQUEST_0_SHIPTOZIP', 'PAYMENTREQUEST_9_SHIPTOZIP'),
            array('getPaymentrequestNShiptocountrycode', 'setPaymentrequestNShiptocountrycode', 'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE', 'PAYMENTREQUEST_9_SHIPTOCOUNTRYCODE'),
            array('getPaymentrequestNShiptophonenum', 'setPaymentrequestNShiptophonenum', 'PAYMENTREQUEST_0_SHIPTOPHONENUM', 'PAYMENTREQUEST_9_SHIPTOPHONENUM'),
            array('getPaymentrequestNAddressstatus', 'setPaymentrequestNAddressstatus', 'PAYMENTREQUEST_0_ADDRESSSTATUS', 'PAYMENTREQUEST_9_ADDRESSSTATUS'),
            array('getPaymentrequestNAmt', 'setPaymentrequestNAmt', 'PAYMENTREQUEST_0_AMT', 'PAYMENTREQUEST_9_AMT'),
            array('getPaymentrequestNCurrencycode', 'setPaymentrequestNCurrencycode', 'PAYMENTREQUEST_0_CURRENCYCODE', 'PAYMENTREQUEST_9_CURRENCYCODE'),
            array('getPaymentrequestNItemamt', 'setPaymentrequestNItemamt', 'PAYMENTREQUEST_0_ITEMAMT', 'PAYMENTREQUEST_9_ITEMAMT'),
            array('getPaymentrequestNShippingamt', 'setPaymentrequestNShippingamt', 'PAYMENTREQUEST_0_SHIPPINGAMT', 'PAYMENTREQUEST_9_SHIPPINGAMT'),
            array('getPaymentrequestNInsuranceamt', 'setPaymentrequestNInsuranceamt', 'PAYMENTREQUEST_0_INSURANCEAMT', 'PAYMENTREQUEST_9_INSURANCEAMT'),
            array('getPaymentrequestNShipdiscamt', 'setPaymentrequestNShipdiscamt', 'PAYMENTREQUEST_0_SHIPDISCAMT', 'PAYMENTREQUEST_9_SHIPDISCAMT'),
            array('getPaymentrequestNInsuranceoptionoffered', 'setPaymentrequestNInsuranceoptionoffered', 'PAYMENTREQUEST_0_INSURANCEOPTIONOFFERED', 'PAYMENTREQUEST_9_INSURANCEOPTIONOFFERED'),
            array('getPaymentrequestNHandlingamt', 'setPaymentrequestNHandlingamt', 'PAYMENTREQUEST_0_HANDLINGAMT', 'PAYMENTREQUEST_9_HANDLINGAMT'),
            array('getPaymentrequestNTaxamt', 'setPaymentrequestNTaxamt', 'PAYMENTREQUEST_0_TAXAMT', 'PAYMENTREQUEST_9_TAXAMT'),
            array('getPaymentrequestNDesc', 'setPaymentrequestNDesc', 'PAYMENTREQUEST_0_DESC', 'PAYMENTREQUEST_9_DESC'),
            array('getPaymentrequestNCustom', 'setPaymentrequestNCustom', 'PAYMENTREQUEST_0_CUSTOM', 'PAYMENTREQUEST_9_CUSTOM'),
            array('getPaymentrequestNInvnum', 'setPaymentrequestNInvnum', 'PAYMENTREQUEST_0_INVNUM', 'PAYMENTREQUEST_9_INVNUM'),
            array('getPaymentrequestNNotifyurl', 'setPaymentrequestNNotifyurl', 'PAYMENTREQUEST_0_NOTIFYURL', 'PAYMENTREQUEST_9_NOTIFYURL'),
            array('getPaymentrequestNNotetext', 'setPaymentrequestNNotetext', 'PAYMENTREQUEST_0_NOTETEXT', 'PAYMENTREQUEST_9_NOTETEXT'),
            array('getPaymentrequestNTransactionid', 'setPaymentrequestNTransactionid', 'PAYMENTREQUEST_0_TRANSACTIONID', 'PAYMENTREQUEST_9_TRANSACTIONID'),
            array('getPaymentrequestNAllowedpaymentmethod', 'setPaymentrequestNAllowedpaymentmethod', 'PAYMENTREQUEST_0_ALLOWEDPAYMENTMETHOD', 'PAYMENTREQUEST_9_ALLOWEDPAYMENTMETHOD'),
            array('getPaymentrequestNPaymentrequestid', 'setPaymentrequestNPaymentrequestid', 'PAYMENTREQUEST_0_PAYMENTREQUESTID', 'PAYMENTREQUEST_9_PAYMENTREQUESTID'),
            array('getPaymentrequestNPaymentaction', 'setPaymentrequestNPaymentaction', 'PAYMENTREQUEST_0_PAYMENTACTION', 'PAYMENTREQUEST_9_PAYMENTACTION'),
            array('getPaymentrequestNPaymentstatus', 'setPaymentrequestNPaymentstatus', 'PAYMENTREQUEST_0_PAYMENTSTATUS', 'PAYMENTREQUEST_9_PAYMENTSTATUS'),
            array('getPaymentrequestNExchangerate', 'setPaymentrequestNExchangerate', 'PAYMENTREQUEST_0_EXCHANGERATE', 'PAYMENTREQUEST_9_EXCHANGERATE'),
            array('getPaymentrequestNSettleamt', 'setPaymentrequestNSettleamt', 'PAYMENTREQUEST_0_SETTLEAMT', 'PAYMENTREQUEST_9_SETTLEAMT'),
            array('getPaymentrequestNFeeamt', 'setPaymentrequestNFeeamt', 'PAYMENTREQUEST_0_FEEAMT', 'PAYMENTREQUEST_9_FEEAMT'),
            array('getPaymentrequestNOrdertime', 'setPaymentrequestNOrdertime', 'PAYMENTREQUEST_0_ORDERTIME', 'PAYMENTREQUEST_9_ORDERTIME'),
            array('getPaymentrequestNPaymenttype', 'setPaymentrequestNPaymenttype', 'PAYMENTREQUEST_0_PAYMENTTYPE', 'PAYMENTREQUEST_9_PAYMENTTYPE'),
            array('getPaymentrequestNTransactiontype', 'setPaymentrequestNTransactiontype', 'PAYMENTREQUEST_0_TRANSACTIONTYPE', 'PAYMENTREQUEST_9_TRANSACTIONTYPE'),
            array('getPaymentrequestNReceiptid', 'setPaymentrequestNReceiptid', 'PAYMENTREQUEST_0_RECEIPTID', 'PAYMENTREQUEST_9_RECEIPTID'),
            array('getPaymentrequestNParenttransactionid', 'setPaymentrequestNParenttransactionid', 'PAYMENTREQUEST_0_PARENTTRANSACTIONID', 'PAYMENTREQUEST_9_PARENTTRANSACTIONID'),
            array('getPaymentrequestNPendingreason', 'setPaymentrequestNPendingreason', 'PAYMENTREQUEST_0_PENDINGREASON', 'PAYMENTREQUEST_9_PENDINGREASON'),
            array('getPaymentrequestNReasoncode', 'setPaymentrequestNReasoncode', 'PAYMENTREQUEST_0_REASONCODE', 'PAYMENTREQUEST_9_REASONCODE'),
            array('getLSeveritycoden', 'setLSeveritycoden', 'L_SEVERITYCODE0', 'L_SEVERITYCODE9'),
            array('getLLongmessagen', 'setLLongmessagen', 'L_LONGMESSAGE0', 'L_LONGMESSAGE9'),
            array('getLShortmessagen', 'setLShortmessagen', 'L_SHORTMESSAGE0', 'L_SHORTMESSAGE9'),
            array('getLErrorcoden', 'setLErrorcoden', 'L_ERRORCODE0', 'L_ERRORCODE9'),
        );
    }
    
    /**
     * @test
     * 
     * @dataProvider provideStringFields
     */
    public function shouldAllowSetStringValue($getter, $setter, $value, $paypalName)
    {
        $instruction = new Instruction();
        
        $instruction->$setter($value);
    }

    /**
     * @test
     *
     * @dataProvider provideStringFields
     */
    public function shouldAllowGetPreviouslySetStringValue($getter, $setter, $value, $paypalName)
    {
        $instruction = new Instruction();

        $instruction->$setter($value);

        $this->assertEquals($value, $instruction->$getter());
    }

    /**
     * @test
     *
     * @dataProvider provideStringFields
     */
    public function shouldSetStringValueFromNvp($getter, $setter, $value, $paypalName)
    {
        $instruction = new Instruction();

        $instruction->fromNvp(array(
            $paypalName => $value
        ));
        
        $this->assertEquals($value, $instruction->$getter());
    }

    /**
     * @test
     *
     * @dataProvider provideStringFields
     */
    public function shouldToNvpStringValue($getter, $setter, $value, $paypalName)
    {
        $instruction = new Instruction();

        $instruction->$setter($value);

        $nvp = $instruction->toNvp();
        $this->assertInternalType('array', $nvp);
        $this->assertArrayHasKey($paypalName, $nvp);
        $this->assertEquals($value, $nvp[$paypalName]);
    }

    /**
     * @test
     * 
     * @dataProvider provideArrayFields
     */
    public function shouldAllowSetArrayValue($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';
        
        $instruction = new Instruction();

        $instruction->$setter(0, $value);
        $instruction->$setter(9, $value);
    }

    /**
     * @test
     *
     * @dataProvider provideArrayFields
     */
    public function shouldAllowGetPreviouslySetArrayValue($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';
        
        $instruction = new Instruction();

        $instruction->$setter(0, $value);
        $instruction->$setter(9, $value);

        $this->assertEquals($value, $instruction->$getter(0));
        $this->assertEquals($value, $instruction->$getter(9));
    }

    /**
     * @test
     *
     * @dataProvider provideArrayFields
     */
    public function shouldGetNullIfNotSetArrayValue($getter, $setter, $paypalName0, $paypalName9)
    {
        $instruction = new Instruction();

        $this->assertNull($instruction->$getter(0));
        $this->assertNull($instruction->$getter(9));
    }

    /**
     * @test
     *
     * @dataProvider provideArrayFields
     */
    public function shouldGetArrayValueAsArrayIfNNotSet($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';
        $expectedResult = array(
            0 => $value,
            9 => $value
        );
        
        $instruction = new Instruction();

        $instruction->$setter(0, $value);
        $instruction->$setter(9, $value);

        $this->assertEquals($expectedResult, $instruction->$getter());
    }

    /**
     * @test
     *
     * @dataProvider provideArrayFields
     */
    public function shouldFromNvpArrayValue($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';
        
        $instruction = new Instruction();
        
        $instruction->fromNvp(array(
            $paypalName0 => $value,
            $paypalName9 => $value
        ));

        $this->assertEquals($value, $instruction->$getter(0));
        $this->assertEquals($value, $instruction->$getter(9));
    }

    /**
     * @test
     *
     * @dataProvider provideArrayFields
     */
    public function shouldToNvpArrayValue($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';
        
        $instruction = new Instruction();

        $instruction->$setter(0, $value);
        $instruction->$setter(9, $value);

        $nvp = $instruction->toNvp();
        $this->assertInternalType('array', $nvp);
        
        $this->assertArrayHasKey($paypalName0, $nvp);
        $this->assertEquals($value, $nvp[$paypalName0]);
        
        $this->assertArrayHasKey($paypalName9, $nvp);
        $this->assertEquals($value, $nvp[$paypalName9]);
    }

    /**
     * @test
     */
    public function shouldAllowToUseResponseInFromNvp()
    {
        $response = new Response;
        $response['TOKEN'] = 'theToken';
        $response['PAYMENTREQUEST_0_AMT'] = 'theAmt';
        $response['PAYMENTREQUEST_9_CURRENCYCODE'] = 'theCurrency';
        
        $instruction = new Instruction();
        $instruction->fromNvp($response);
        
        $this->assertEquals('theToken', $instruction->getToken());
        $this->assertEquals('theAmt', $instruction->getPaymentrequestNAmt(0));
        $this->assertEquals('theCurrency', $instruction->getPaymentrequestNCurrencycode(9));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage Should be an array of an object implemented Traversable interface.
     */
    public function throwIfInvalidArgumentForFromNvp()
    {
        $instruction = new Instruction();
        $instruction->fromNvp(new \stdClass);
    }
}
