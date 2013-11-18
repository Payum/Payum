<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Model;

use Payum\Paypal\ExpressCheckout\Nvp\Model\PaymentDetails;

class PaymentDetailsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModel()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Model\PaymentDetails');

        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Model\BaseModel'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PaymentDetails();
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
            array('getNoshipping', 'setNoshipping', 'theValue', 'NOSHIPPING'),
            array('getReqconfirmshipping', 'setReqconfirmshipping', 'theValue', 'REQCONFIRMSHIPPING'),
        );
    }

    public function provideArrayFields()
    {
        return array(
            array('getPaymentrequestShiptostreet', 'setPaymentrequestShiptostreet', 'PAYMENTREQUEST_0_SHIPTOSTREET', 'PAYMENTREQUEST_9_SHIPTOSTREET'),
            array('getPaymentrequestShiptoname', 'setPaymentrequestShiptoname', 'PAYMENTREQUEST_0_SHIPTONAME', 'PAYMENTREQUEST_9_SHIPTONAME'),
            array('getPaymentrequestShiptostreet2', 'setPaymentrequestShiptostreet2', 'PAYMENTREQUEST_0_SHIPTOSTREET2', 'PAYMENTREQUEST_9_SHIPTOSTREET2'),
            array('getPaymentrequestShiptocity', 'setPaymentrequestShiptocity', 'PAYMENTREQUEST_0_SHIPTOCITY', 'PAYMENTREQUEST_9_SHIPTOCITY'),
            array('getPaymentrequestShiptostate', 'setPaymentrequestShiptostate', 'PAYMENTREQUEST_0_SHIPTOSTATE', 'PAYMENTREQUEST_9_SHIPTOSTATE'),
            array('getPaymentrequestShiptozip', 'setPaymentrequestShiptozip', 'PAYMENTREQUEST_0_SHIPTOZIP', 'PAYMENTREQUEST_9_SHIPTOZIP'),
            array('getPaymentrequestShiptocountrycode', 'setPaymentrequestShiptocountrycode', 'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE', 'PAYMENTREQUEST_9_SHIPTOCOUNTRYCODE'),
            array('getPaymentrequestShiptophonenum', 'setPaymentrequestShiptophonenum', 'PAYMENTREQUEST_0_SHIPTOPHONENUM', 'PAYMENTREQUEST_9_SHIPTOPHONENUM'),
            array('getPaymentrequestAddressstatus', 'setPaymentrequestAddressstatus', 'PAYMENTREQUEST_0_ADDRESSSTATUS', 'PAYMENTREQUEST_9_ADDRESSSTATUS'),
            array('getPaymentrequestAmt', 'setPaymentrequestAmt', 'PAYMENTREQUEST_0_AMT', 'PAYMENTREQUEST_9_AMT'),
            array('getPaymentrequestCurrencycode', 'setPaymentrequestCurrencycode', 'PAYMENTREQUEST_0_CURRENCYCODE', 'PAYMENTREQUEST_9_CURRENCYCODE'),
            array('getPaymentrequestItemamt', 'setPaymentrequestItemamt', 'PAYMENTREQUEST_0_ITEMAMT', 'PAYMENTREQUEST_9_ITEMAMT'),
            array('getPaymentrequestShippingamt', 'setPaymentrequestShippingamt', 'PAYMENTREQUEST_0_SHIPPINGAMT', 'PAYMENTREQUEST_9_SHIPPINGAMT'),
            array('getPaymentrequestInsuranceamt', 'setPaymentrequestInsuranceamt', 'PAYMENTREQUEST_0_INSURANCEAMT', 'PAYMENTREQUEST_9_INSURANCEAMT'),
            array('getPaymentrequestShipdiscamt', 'setPaymentrequestShipdiscamt', 'PAYMENTREQUEST_0_SHIPDISCAMT', 'PAYMENTREQUEST_9_SHIPDISCAMT'),
            array('getPaymentrequestInsuranceoptionoffered', 'setPaymentrequestInsuranceoptionoffered', 'PAYMENTREQUEST_0_INSURANCEOPTIONOFFERED', 'PAYMENTREQUEST_9_INSURANCEOPTIONOFFERED'),
            array('getPaymentrequestHandlingamt', 'setPaymentrequestHandlingamt', 'PAYMENTREQUEST_0_HANDLINGAMT', 'PAYMENTREQUEST_9_HANDLINGAMT'),
            array('getPaymentrequestTaxamt', 'setPaymentrequestTaxamt', 'PAYMENTREQUEST_0_TAXAMT', 'PAYMENTREQUEST_9_TAXAMT'),
            array('getPaymentrequestDesc', 'setPaymentrequestDesc', 'PAYMENTREQUEST_0_DESC', 'PAYMENTREQUEST_9_DESC'),
            array('getPaymentrequestCustom', 'setPaymentrequestCustom', 'PAYMENTREQUEST_0_CUSTOM', 'PAYMENTREQUEST_9_CUSTOM'),
            array('getPaymentrequestInvnum', 'setPaymentrequestInvnum', 'PAYMENTREQUEST_0_INVNUM', 'PAYMENTREQUEST_9_INVNUM'),
            array('getPaymentrequestNotifyurl', 'setPaymentrequestNotifyurl', 'PAYMENTREQUEST_0_NOTIFYURL', 'PAYMENTREQUEST_9_NOTIFYURL'),
            array('getPaymentrequestNotetext', 'setPaymentrequestNotetext', 'PAYMENTREQUEST_0_NOTETEXT', 'PAYMENTREQUEST_9_NOTETEXT'),
            array('getPaymentrequestTransactionid', 'setPaymentrequestTransactionid', 'PAYMENTREQUEST_0_TRANSACTIONID', 'PAYMENTREQUEST_9_TRANSACTIONID'),
            array('getPaymentrequestAllowedpaymentmethod', 'setPaymentrequestAllowedpaymentmethod', 'PAYMENTREQUEST_0_ALLOWEDPAYMENTMETHOD', 'PAYMENTREQUEST_9_ALLOWEDPAYMENTMETHOD'),
            array('getPaymentrequestPaymentrequestid', 'setPaymentrequestPaymentrequestid', 'PAYMENTREQUEST_0_PAYMENTREQUESTID', 'PAYMENTREQUEST_9_PAYMENTREQUESTID'),
            array('getPaymentrequestPaymentaction', 'setPaymentrequestPaymentaction', 'PAYMENTREQUEST_0_PAYMENTACTION', 'PAYMENTREQUEST_9_PAYMENTACTION'),
            array('getPaymentrequestPaymentstatus', 'setPaymentrequestPaymentstatus', 'PAYMENTREQUEST_0_PAYMENTSTATUS', 'PAYMENTREQUEST_9_PAYMENTSTATUS'),
            array('getPaymentrequestExchangerate', 'setPaymentrequestExchangerate', 'PAYMENTREQUEST_0_EXCHANGERATE', 'PAYMENTREQUEST_9_EXCHANGERATE'),
            array('getPaymentrequestSettleamt', 'setPaymentrequestSettleamt', 'PAYMENTREQUEST_0_SETTLEAMT', 'PAYMENTREQUEST_9_SETTLEAMT'),
            array('getPaymentrequestFeeamt', 'setPaymentrequestFeeamt', 'PAYMENTREQUEST_0_FEEAMT', 'PAYMENTREQUEST_9_FEEAMT'),
            array('getPaymentrequestOrdertime', 'setPaymentrequestOrdertime', 'PAYMENTREQUEST_0_ORDERTIME', 'PAYMENTREQUEST_9_ORDERTIME'),
            array('getPaymentrequestPaymenttype', 'setPaymentrequestPaymenttype', 'PAYMENTREQUEST_0_PAYMENTTYPE', 'PAYMENTREQUEST_9_PAYMENTTYPE'),
            array('getPaymentrequestTransactiontype', 'setPaymentrequestTransactiontype', 'PAYMENTREQUEST_0_TRANSACTIONTYPE', 'PAYMENTREQUEST_9_TRANSACTIONTYPE'),
            array('getPaymentrequestReceiptid', 'setPaymentrequestReceiptid', 'PAYMENTREQUEST_0_RECEIPTID', 'PAYMENTREQUEST_9_RECEIPTID'),
            array('getPaymentrequestParenttransactionid', 'setPaymentrequestParenttransactionid', 'PAYMENTREQUEST_0_PARENTTRANSACTIONID', 'PAYMENTREQUEST_9_PARENTTRANSACTIONID'),
            array('getPaymentrequestPendingreason', 'setPaymentrequestPendingreason', 'PAYMENTREQUEST_0_PENDINGREASON', 'PAYMENTREQUEST_9_PENDINGREASON'),
            array('getLBillingtype', 'setLBillingtype', 'L_BILLINGTYPE0', 'L_BILLINGTYPE9'),
            array('getLBillingagreementdescription', 'setLBillingagreementdescription', 'L_BILLINGAGREEMENTDESCRIPTION0', 'L_BILLINGAGREEMENTDESCRIPTION9'),
            
            array('getLSeveritycoden', 'setLSeveritycoden', 'L_SEVERITYCODE0', 'L_SEVERITYCODE9'),
            array('getLLongmessagen', 'setLLongmessagen', 'L_LONGMESSAGE0', 'L_LONGMESSAGE9'),
            array('getLShortmessagen', 'setLShortmessagen', 'L_SHORTMESSAGE0', 'L_SHORTMESSAGE9'),
            array('getLErrorcoden', 'setLErrorcoden', 'L_ERRORCODE0', 'L_ERRORCODE9'),
        );
    }

    public static function provideMultiArrayValues()
    {
        return array(
            array('getLPaymentrequestName', 'setLPaymentrequestName', 'L_PAYMENTREQUEST_0_NAME0', 'L_PAYMENTREQUEST_9_NAME9'),
            array('getLPaymentrequestDesc', 'setLPaymentrequestDesc', 'L_PAYMENTREQUEST_0_DESC0', 'L_PAYMENTREQUEST_9_DESC9'),
            array('getLPaymentrequestQty', 'setLPaymentrequestQty', 'L_PAYMENTREQUEST_0_QTY0', 'L_PAYMENTREQUEST_9_QTY9'),
            array('getLPaymentrequestAmt', 'setLPaymentrequestAmt', 'L_PAYMENTREQUEST_0_AMT0', 'L_PAYMENTREQUEST_9_AMT9'),
            array('getLPaymentrequestItemcategory', 'setLPaymentrequestItemcategory', 'L_PAYMENTREQUEST_0_ITEMCATEGORY0', 'L_PAYMENTREQUEST_9_ITEMCATEGORY9'),
        );
    }

    public static function provideMultiArrayValuesAndMNotEqualsN()
    {
        return array(
            array('getLPaymentrequestName', 'setLPaymentrequestName', 'L_PAYMENTREQUEST_0_NAME0', 'L_PAYMENTREQUEST_0_NAME1'),
            array('getLPaymentrequestDesc', 'setLPaymentrequestDesc', 'L_PAYMENTREQUEST_0_DESC0', 'L_PAYMENTREQUEST_0_DESC1'),
            array('getLPaymentrequestQty', 'setLPaymentrequestQty', 'L_PAYMENTREQUEST_0_QTY0', 'L_PAYMENTREQUEST_0_QTY1'),
            array('getLPaymentrequestAmt', 'setLPaymentrequestAmt', 'L_PAYMENTREQUEST_0_AMT0', 'L_PAYMENTREQUEST_1_AMT1'),
            array('getLPaymentrequestItemcategory', 'setLPaymentrequestItemcategory', 'L_PAYMENTREQUEST_0_ITEMCATEGORY0', 'L_PAYMENTREQUEST_0_ITEMCATEGORY1'),
        );
     }

    /**
     * @test
     *
     * @dataProvider provideStringFields
     */
    public function shouldAllowSetStringValue($getter, $setter, $value, $paypalName)
    {
        $details = new PaymentDetails();

        $details->$setter($value);
    }

    /**
     * @test
     *
     * @dataProvider provideStringFields
     */
    public function shouldAllowGetPreviouslySetStringValue($getter, $setter, $value, $paypalName)
    {
        $details = new PaymentDetails();

        $details->$setter($value);

        $this->assertEquals($value, $details->$getter());
    }

    /**
     * @test
     *
     * @dataProvider provideStringFields
     */
    public function shouldAllowSetStringValueInArrayWay($getter, $setter, $value, $paypalName)
    {
        $details = new PaymentDetails();

        $details[$paypalName] = $value;

        $this->assertEquals($value, $details->$getter());
    }

    /**
     * @test
     *
     * @dataProvider provideStringFields
     */
    public function shouldAllowGetStringValueInArrayWay($getter, $setter, $value, $paypalName)
    {
        $details = new PaymentDetails();

        $details->$setter($value);

        $this->assertTrue(isset($details[$paypalName]));
        $this->assertEquals($value, $details[$paypalName]);
    }

    /**
     * @test
     *
     * @dataProvider provideStringFields
     */
    public function shouldAllowSetAndGetStringValueInArrayWay($getter, $setter, $value, $paypalName)
    {
        $details = new PaymentDetails();

        $details[$paypalName] = $value;

        $this->assertTrue(isset($details[$paypalName]));
        $this->assertEquals($value, $details[$paypalName]);
    }

    /**
     * @test
     *
     * @dataProvider provideArrayFields
     */
    public function shouldAllowSetArrayValue($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';

        $details = new PaymentDetails();

        $details->$setter(0, $value);
        $details->$setter(9, $value);
    }

    /**
     * @test
     *
     * @dataProvider provideArrayFields
     */
    public function shouldAllowGetPreviouslySetArrayValue($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';

        $details = new PaymentDetails();

        $details->$setter(0, $value);
        $details->$setter(9, $value);

        $this->assertEquals($value, $details->$getter(0));
        $this->assertEquals($value, $details->$getter(9));
    }

    /**
     * @test
     *
     * @dataProvider provideArrayFields
     */
    public function shouldGetNullIfNotSetArrayValue($getter, $setter, $paypalName0, $paypalName9)
    {
        $details = new PaymentDetails();

        $this->assertNull($details->$getter(0));
        $this->assertNull($details->$getter(9));
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

        $details = new PaymentDetails();

        $details->$setter(0, $value);
        $details->$setter(9, $value);

        $this->assertEquals($expectedResult, $details->$getter());
    }

    /**
     * @test
     *
     * @dataProvider provideArrayFields
     */
    public function shouldAllowSetArrayValueInArrayWay($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';

        $details = new PaymentDetails();

        $details[$paypalName0] = $value;
        $details[$paypalName9] = $value;

        $this->assertEquals($value, $details->$getter(0));
        $this->assertEquals($value, $details->$getter(9));
    }

    /**
     * @test
     *
     * @dataProvider provideArrayFields
     */
    public function shouldAllowGetArrayValueInArrayWay($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';

        $details = new PaymentDetails();

        $details->$setter(0, $value);
        $details->$setter(9, $value);

        $this->assertTrue(isset($details[$paypalName0]));
        $this->assertEquals($value, $details[$paypalName0]);

        $this->assertTrue(isset($details[$paypalName9]));
        $this->assertEquals($value, $details[$paypalName9]);
    }

    /**
     * @test
     *
     * @dataProvider provideArrayFields
     */
    public function shouldAllowSetAndGetArrayValueInArrayWay($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';

        $details = new PaymentDetails();

        $details[$paypalName0] = $value;
        $details[$paypalName9] = $value;

        $this->assertTrue(isset($details[$paypalName0]));
        $this->assertEquals($value, $details[$paypalName0]);

        $this->assertTrue(isset($details[$paypalName9]));
        $this->assertEquals($value, $details[$paypalName9]);
    }

    /**
     * @test
     *
     * @dataProvider provideMultiArrayValues
     */
    public function shouldAllowSetMultiArrayValue($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';

        $details = new PaymentDetails();

        $details->$setter(0, 0, $value);
        $details->$setter(9, 9, $value);
    }

    /**
     * @test
     *
     * @dataProvider provideMultiArrayValues
     */
    public function shouldAllowGetPreviouslySetMultiArrayValue($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';

        $details = new PaymentDetails();

        $details->$setter(0, 0, $value);
        $details->$setter(9, 9, $value);

        $this->assertEquals($value, $details->$getter(0, 0));
        $this->assertEquals($value, $details->$getter(9, 9));
    }

    /**
     * @test
     *
     * @dataProvider provideMultiArrayValues
     */
    public function shouldGetNullIfNotSetMultiArrayValue($getter, $setter, $paypalName0, $paypalName9)
    {
        $details = new PaymentDetails();

        $this->assertNull($details->$getter(0, 0));
        $this->assertNull($details->$getter(9, 9));
    }

    /**
     * @test
     *
     * @dataProvider provideMultiArrayValues
     */
    public function shouldAllowSetMultiArrayValueInArrayWay($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';

        $details = new PaymentDetails();

        $details[$paypalName0] = $value;
        $details[$paypalName9] = $value;

        $this->assertEquals($value, $details->$getter(0, 0));
        $this->assertEquals($value, $details->$getter(9, 9));
    }

    /**
     * @test
     */
    public function shouldAllowSetZeroPaymentrequestAmount()
    {
        $value = 0;

        $details = new PaymentDetails;

        $details['PAYMENTREQUEST_0_AMT'] = $value;

        $this->assertEquals($value, $details->getPaymentrequestAmt(0));
        $this->assertEquals($value, $details['PAYMENTREQUEST_0_AMT']);
        
        $detailsAsArray = iterator_to_array($details);
        
        $this->assertArrayHasKey('PAYMENTREQUEST_0_AMT', $detailsAsArray);
        $this->assertEquals($value, $detailsAsArray['PAYMENTREQUEST_0_AMT']);
    }

    /**
     * @test
     *
     * @dataProvider provideMultiArrayValues
     */
    public function shouldAllowGetMultiArrayValueInArrayWay($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';

        $details = new PaymentDetails();

        $details->$setter(0, 0, $value);
        $details->$setter(9, 9, $value);

        $this->assertTrue(isset($details[$paypalName0]));
        $this->assertEquals($value, $details[$paypalName0]);

        $this->assertTrue(isset($details[$paypalName9]));
        $this->assertEquals($value, $details[$paypalName9]);
    }

    /**
     * @test
     *
     * @dataProvider provideMultiArrayValues
     */
    public function shouldAllowSetAndGetMultiArrayValueInArrayWay($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';

        $details = new PaymentDetails();

        $details[$paypalName0] = $value;
        $details[$paypalName9] = $value;

        $this->assertTrue(isset($details[$paypalName0]));
        $this->assertEquals($value, $details[$paypalName0]);

        $this->assertTrue(isset($details[$paypalName9]));
        $this->assertEquals($value, $details[$paypalName9]);
    }

    /**
     * @test
     */
    public function shouldAllowSetCustomValue()
    {
        $value = 'theValue';

        $details = new PaymentDetails();
        $details['foo'] = $value;

        $this->assertTrue(isset($details['foo']));
        $this->assertEquals($value, $details['foo']);

        unset($details['foo']);

        $this->assertFalse(isset($details['foo']));
        $this->assertNull($details['foo']);
    }

    /**
     * @group bugfix
     * @test
     * @dataProvider provideMultiArrayValuesAndMNotEqualsN
     */
    public function shouldCorrectlySetOffsetMultiItemValues($getter, $setter, $paypalName00, $paypalName01)
    {
        $value = 'theValue';

        $details = new PaymentDetails();

        $details->$setter(0, 0, $value);
        $this->assertEquals($value, $details->$getter(0,0, $value));
        $details->$setter(0, 1, $value);
        $this->assertEquals($value, $details->$getter(0,1, $value));
    }

    /**
     * @group bugfix
     * @test
     * @dataProvider provideMultiArrayValuesAndMNotEqualsN
     */
    public function shouldCorrectlySetOffsetMultiItemArrayWay($getter, $setter, $paypalName00, $paypalName01)
    {
        $value = 'theValue';

        $details = new PaymentDetails();
        $details[$paypalName00] = $value;
        $details[$paypalName01] = $value;

        $this->assertEquals($value, $details[$paypalName00]);
        $this->assertEquals($value, $details[$paypalName01]);
    }
}