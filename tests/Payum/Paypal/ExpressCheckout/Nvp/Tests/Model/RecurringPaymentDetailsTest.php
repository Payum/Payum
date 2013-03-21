<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Model;

use Payum\Paypal\ExpressCheckout\Nvp\Model\RecurringPaymentDetails;

class RecurringPaymentDetailsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModel()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Model\RecurringPaymentDetails');

        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Model\BaseModel'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new RecurringPaymentDetails();
    }

    public function provideStringFields()
    {
        return array(
            array('getToken', 'setToken', 'theValue', 'TOKEN'),
            array('getStatus', 'setStatus', 'theValue', 'STATUS'),
            array('getSubscribername', 'setSubscribername', 'theValue', 'SUBSCRIBERNAME'),
            array('getSubscribername', 'setSubscribername', 'theValue', 'SUBSCRIBERNAME'),
            array('getProfilereference', 'setProfilereference', 'theValue', 'PROFILEREFERENCE'),
            array('getDesc', 'setDesc', 'theValue', 'DESC'),
            array('getMaxfailedpayments', 'setMaxfailedpayments', 'theValue', 'MAXFAILEDPAYMENTS'),
            array('getAutobilloutamt', 'setAutobilloutamt', 'theValue', 'AUTOBILLOUTAMT'),
            array('getBillingperiod', 'setBillingperiod', 'theValue', 'BILLINGPERIOD'),
            array('getBillingfrequency', 'setBillingfrequency', 'theValue', 'BILLINGFREQUENCY'),
            array('getTotalbillingcycles', 'setTotalbillingcycles', 'theValue', 'TOTALBILLINGCYCLES'),
            array('getAmt', 'setAmt', 'theValue', 'AMT'),
            array('getTrialbillingperiod', 'setTrialbillingperiod', 'theValue', 'TRIALBILLINGPERIOD'),
            array('getTrialbillingfrequency', 'setTrialbillingfrequency', 'theValue', 'TRIALBILLINGFREQUENCY'),
            array('getTrialtotalbillingcycles', 'setTrialtotalbillingcycles', 'theValue', 'TRIALTOTALBILLINGCYCLES'),
            array('getTrialamt', 'setTrialamt', 'theValue', 'TRIALAMT'),
            array('getCurrencycode', 'setCurrencycode', 'theValue', 'CURRENCYCODE'),
            array('getShippingamt', 'setShippingamt', 'theValue', 'SHIPPINGAMT'),
            array('getTaxamt', 'setTaxamt', 'theValue', 'TAXAMT'),
            array('getInitamt', 'setInitamt', 'theValue', 'INITAMT'),
            array('getFailedinitamtaction', 'setFailedinitamtaction', 'theValue', 'FAILEDINITAMTACTION'),
            array('getShiptoname', 'setShiptoname', 'theValue', 'SHIPTONAME'),
            array('getShiptostreet', 'setShiptostreet', 'theValue', 'SHIPTOSTREET'),
            array('getShiptostreet2', 'setShiptostreet2', 'theValue', 'SHIPTOSTREET2'),
            array('getShiptocity', 'setShiptocity', 'theValue', 'SHIPTOCITY'),
            array('getShiptostate', 'setShiptostate', 'theValue', 'SHIPTOSTATE'),
            array('getShiptozip', 'setShiptozip', 'theValue', 'SHIPTOZIP'),
            array('getShiptocountry', 'setShiptocountry', 'theValue', 'SHIPTOCOUNTRY'),
            array('getShiptophonenum', 'setShiptophonenum', 'theValue', 'SHIPTOPHONENUM'),
            array('getCreditcardtype', 'setCreditcardtype', 'theValue', 'CREDITCARDTYPE'),
            array('getAcct', 'setAcct', 'theValue', 'ACCT'),
            array('getExpdate', 'setExpdate', 'theValue', 'EXPDATE'),
            array('getCvv2', 'setCvv2', 'theValue', 'CVV2'),
            array('getStartdate', 'setStartdate', 'theValue', 'STARTDATE'),
            array('getIssuenumber', 'setIssuenumber', 'theValue', 'ISSUENUMBER'),
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
            array('getStreet', 'setStreet', 'theValue', 'STREET'),
            array('getStreet2', 'setStreet2', 'theValue', 'STREET2'),
            array('getCity', 'setCity', 'theValue', 'CITY'),
            array('getState', 'setState', 'theValue', 'STATE'),
            array('getZip', 'setZip', 'theValue', 'ZIP'),
            array('getProfileid', 'setProfileid', 'theValue', 'PROFILEID'),
            array('getProfilestatus', 'setProfilestatus', 'theValue', 'PROFILESTATUS'),
            array('getTimestamp', 'setTimestamp', 'theValue', 'TIMESTAMP'),
            array('getCorrelationid', 'setCorrelationid', 'theValue', 'CORRELATIONID'),
            array('getVersion', 'setVersion', 'theValue', 'VERSION'),
            array('getBuild', 'setBuild', 'theValue', 'BUILD'),
            array('getAck', 'setAck', 'theValue', 'ACK'),
            array('getAggregateamount', 'setAggregateamount', 'theValue', 'AGGREGATEAMOUNT'),
            array('getAggregateoptionalamount', 'setAggregateoptionalamount', 'theValue', 'AGGREGATEOPTIONALAMOUNT'),
            array('getFinalpaymentduedate', 'setFinalpaymentduedate', 'theValue', 'FINALPAYMENTDUEDATE'),
            array('getAddressstatus', 'setAddressstatus', 'theValue', 'ADDRESSSTATUS'),
            array('getRegularbillingperiod', 'setRegularbillingperiod', 'theValue', 'REGULARBILLINGPERIOD'),
            array('getRegularbillingfrequency', 'setRegularbillingfrequency', 'theValue', 'REGULARBILLINGFREQUENCY'),
            array('getRegulartotalbillingcycles', 'setRegulartotalbillingcycles', 'theValue', 'REGULARTOTALBILLINGCYCLES'),
            array('getRegularamt', 'setRegularamt', 'theValue', 'REGULARAMT'),
            array('getRegularshippingamt', 'setRegularshippingamt', 'theValue', 'REGULARSHIPPINGAMT'),
            array('getRegulartaxamt', 'setRegulartaxamt', 'theValue', 'REGULARTAXAMT'),
            array('getRegularcurrencycode', 'setRegularcurrencycode', 'theValue', 'REGULARCURRENCYCODE'),
            array('getNextbillingdate', 'setNextbillingdate', 'theValue', 'NEXTBILLINGDATE'),
            array('getNumcylescompleted', 'setNumcylescompleted', 'theValue', 'NUMCYLESCOMPLETED'),
            array('getNumcyclesremaining', 'setNumcyclesremaining', 'theValue', 'NUMCYCLESREMAINING'),
            array('getOutstandingbalance', 'setOutstandingbalance', 'theValue', 'OUTSTANDINGBALANCE'),
            array('getFailedpaymentcount', 'setFailedpaymentcount', 'theValue', 'FAILEDPAYMENTCOUNT'),
            array('getLastpaymentdate', 'setLastpaymentdate', 'theValue', 'LASTPAYMENTDATE'),
            array('getLastpaymentamt', 'setLastpaymentamt', 'theValue', 'LASTPAYMENTAMT'),
            
            
        );
    }

    public function provideArrayFields()
    {
        return array(
            array('getLSeveritycoden', 'setLSeveritycoden', 'L_SEVERITYCODE0', 'L_SEVERITYCODE9'),
            array('getLLongmessagen', 'setLLongmessagen', 'L_LONGMESSAGE0', 'L_LONGMESSAGE9'),
            array('getLShortmessagen', 'setLShortmessagen', 'L_SHORTMESSAGE0', 'L_SHORTMESSAGE9'),
            array('getLErrorcoden', 'setLErrorcoden', 'L_ERRORCODE0', 'L_ERRORCODE9'),
        );
    }

    public static function provideMultiArrayValues()
    {
        return array(
            array('getLPaymentrequestItemcategory', 'setLPaymentrequestItemcategory', 'L_PAYMENTREQUEST_0_ITEMCATEGORY0', 'L_PAYMENTREQUEST_9_ITEMCATEGORY9'),
            array('getLPaymentrequestName', 'setLPaymentrequestName', 'L_PAYMENTREQUEST_0_NAME0', 'L_PAYMENTREQUEST_9_NAME9'),
            array('getLPaymentrequestDesc', 'setLPaymentrequestDesc', 'L_PAYMENTREQUEST_0_DESC0', 'L_PAYMENTREQUEST_9_DESC9'),
            array('getLPaymentrequestQty', 'setLPaymentrequestQty', 'L_PAYMENTREQUEST_0_QTY0', 'L_PAYMENTREQUEST_9_QTY9'),
            array('getLPaymentrequestNumber', 'setLPaymentrequestNumber', 'L_PAYMENTREQUEST_0_NUMBER0', 'L_PAYMENTREQUEST_9_NUMBER9'),
            array('getLPaymentrequestAmt', 'setLPaymentrequestAmt', 'L_PAYMENTREQUEST_0_AMT0', 'L_PAYMENTREQUEST_9_AMT9'),
            array('getLPaymentrequestTaxamt', 'setLPaymentrequestTaxamt', 'L_PAYMENTREQUEST_0_TAXAMT0', 'L_PAYMENTREQUEST_9_TAXAMT9'),
        );
    }

    /**
     * @test
     *
     * @dataProvider provideStringFields
     */
    public function shouldAllowSetStringValue($getter, $setter, $value, $paypalName)
    {
        $instruction = new RecurringPaymentDetails();

        $instruction->$setter($value);
    }

    /**
     * @test
     *
     * @dataProvider provideStringFields
     */
    public function shouldAllowGetPreviouslySetStringValue($getter, $setter, $value, $paypalName)
    {
        $instruction = new RecurringPaymentDetails();

        $instruction->$setter($value);

        $this->assertEquals($value, $instruction->$getter());
    }

    /**
     * @test
     *
     * @dataProvider provideStringFields
     */
    public function shouldAllowSetStringValueInArrayWay($getter, $setter, $value, $paypalName)
    {
        $instruction = new RecurringPaymentDetails();

        $instruction[$paypalName] = $value;

        $this->assertEquals($value, $instruction->$getter());
    }

    /**
     * @test
     *
     * @dataProvider provideStringFields
     */
    public function shouldAllowGetStringValueInArrayWay($getter, $setter, $value, $paypalName)
    {
        $instruction = new RecurringPaymentDetails();

        $instruction->$setter($value);

        $this->assertTrue(isset($instruction[$paypalName]));
        $this->assertEquals($value, $instruction[$paypalName]);
    }

    /**
     * @test
     *
     * @dataProvider provideStringFields
     */
    public function shouldAllowSetAndGetStringValueInArrayWay($getter, $setter, $value, $paypalName)
    {
        $instruction = new RecurringPaymentDetails();

        $instruction[$paypalName] = $value;

        $this->assertTrue(isset($instruction[$paypalName]));
        $this->assertEquals($value, $instruction[$paypalName]);
    }

    /**
     * @test
     *
     * @dataProvider provideArrayFields
     */
    public function shouldAllowSetArrayValue($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';

        $instruction = new RecurringPaymentDetails();

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

        $instruction = new RecurringPaymentDetails();

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
        $instruction = new RecurringPaymentDetails();

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

        $instruction = new RecurringPaymentDetails();

        $instruction->$setter(0, $value);
        $instruction->$setter(9, $value);

        $this->assertEquals($expectedResult, $instruction->$getter());
    }

    /**
     * @test
     *
     * @dataProvider provideArrayFields
     */
    public function shouldAllowSetArrayValueInArrayWay($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';

        $instruction = new RecurringPaymentDetails();

        $instruction[$paypalName0] = $value;
        $instruction[$paypalName9] = $value;

        $this->assertEquals($value, $instruction->$getter(0));
        $this->assertEquals($value, $instruction->$getter(9));
    }

    /**
     * @test
     *
     * @dataProvider provideArrayFields
     */
    public function shouldAllowGetArrayValueInArrayWay($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';

        $instruction = new RecurringPaymentDetails();

        $instruction->$setter(0, $value);
        $instruction->$setter(9, $value);

        $this->assertTrue(isset($instruction[$paypalName0]));
        $this->assertEquals($value, $instruction[$paypalName0]);

        $this->assertTrue(isset($instruction[$paypalName9]));
        $this->assertEquals($value, $instruction[$paypalName9]);
    }

    /**
     * @test
     *
     * @dataProvider provideArrayFields
     */
    public function shouldAllowSetAndGetArrayValueInArrayWay($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';

        $instruction = new RecurringPaymentDetails();

        $instruction[$paypalName0] = $value;
        $instruction[$paypalName9] = $value;

        $this->assertTrue(isset($instruction[$paypalName0]));
        $this->assertEquals($value, $instruction[$paypalName0]);

        $this->assertTrue(isset($instruction[$paypalName9]));
        $this->assertEquals($value, $instruction[$paypalName9]);
    }

    /**
     * @test
     *
     * @dataProvider provideMultiArrayValues
     */
    public function shouldAllowSetMultiArrayValue($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';

        $instruction = new RecurringPaymentDetails();

        $instruction->$setter(0, 0, $value);
        $instruction->$setter(9, 9, $value);
    }

    /**
     * @test
     *
     * @dataProvider provideMultiArrayValues
     */
    public function shouldAllowGetPreviouslySetMultiArrayValue($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';

        $instruction = new RecurringPaymentDetails();

        $instruction->$setter(0, 0, $value);
        $instruction->$setter(9, 9, $value);

        $this->assertEquals($value, $instruction->$getter(0, 0));
        $this->assertEquals($value, $instruction->$getter(9, 9));
    }

    /**
     * @test
     *
     * @dataProvider provideMultiArrayValues
     */
    public function shouldGetNullIfNotSetMultiArrayValue($getter, $setter, $paypalName0, $paypalName9)
    {
        $instruction = new RecurringPaymentDetails();

        $this->assertNull($instruction->$getter(0, 0));
        $this->assertNull($instruction->$getter(9, 9));
    }

    /**
     * @test
     *
     * @dataProvider provideMultiArrayValues
     */
    public function shouldAllowSetMultiArrayValueInArrayWay($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';

        $instruction = new RecurringPaymentDetails();

        $instruction[$paypalName0] = $value;
        $instruction[$paypalName9] = $value;

        $this->assertEquals($value, $instruction->$getter(0, 0));
        $this->assertEquals($value, $instruction->$getter(9, 9));
    }


    /**
     * @test
     *
     * @dataProvider provideMultiArrayValues
     */
    public function shouldAllowGetMultiArrayValueInArrayWay($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';

        $instruction = new RecurringPaymentDetails();

        $instruction->$setter(0, 0, $value);
        $instruction->$setter(9, 9, $value);

        $this->assertTrue(isset($instruction[$paypalName0]));
        $this->assertEquals($value, $instruction[$paypalName0]);

        $this->assertTrue(isset($instruction[$paypalName9]));
        $this->assertEquals($value, $instruction[$paypalName9]);
    }

    /**
     * @test
     *
     * @dataProvider provideMultiArrayValues
     */
    public function shouldAllowSetAndGetMultiArrayValueInArrayWay($getter, $setter, $paypalName0, $paypalName9)
    {
        $value = 'theValue';

        $instruction = new RecurringPaymentDetails();

        $instruction[$paypalName0] = $value;
        $instruction[$paypalName9] = $value;

        $this->assertTrue(isset($instruction[$paypalName0]));
        $this->assertEquals($value, $instruction[$paypalName0]);

        $this->assertTrue(isset($instruction[$paypalName9]));
        $this->assertEquals($value, $instruction[$paypalName9]);
    }
}