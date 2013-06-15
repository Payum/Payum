<?php
namespace Payum\Payex\Tests\Model;

use Payum\Payex\Model\AgreementDetails;

class AgreementDetailsTest extends \PHPUnit_Framework_TestCase
{
    public static function provideFields()
    {
        return array(
            array('merchantRef', 'getMerchantRef', 'setMerchantRef'),
            array('description', 'getDescription', 'setDescription'),
            array('purchaseOperation', 'getPurchaseOperation', 'setPurchaseOperation'),
            array('maxAmount', 'getMaxAmount', 'setMaxAmount'),
            array('startDate', 'getStartDate', 'setStartDate'),
            array('stopDate', 'getStopDate', 'setStopDate'),
            array('errorCode', 'getErrorCode', 'setErrorCode'),
            array('errorDescription', 'getErrorDescription', 'setErrorDescription'),
            array('paramName', 'getParamName', 'setParamName'),
            array('thirdPartyError', 'getThirdPartyError', 'setThirdPartyError'),
            array('agreementRef', 'getAgreementRef', 'setAgreementRef'),
            array('agreementStatus', 'getAgreementStatus', 'setAgreementStatus'),
        );
    }
    
    /**
     * @test
     */
    public function shouldImplementArrayAccessInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Model\AgreementDetails');

        $this->assertTrue($rc->implementsInterface('ArrayAccess'));
    }

    /**
     * @test
     */
    public function shouldImplementIteratorAggregateInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Model\AgreementDetails');

        $this->assertTrue($rc->implementsInterface('IteratorAggregate'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new AgreementDetails;
    }

    /**
     * @test
     * 
     * @dataProvider provideFields
     */
    public function shouldAllowSetField($fieldName, $getter, $setter)
    {
        $details = new AgreementDetails;

        $details->$setter('aValue');
    }

    /**
     * @test
     *
     * @dataProvider provideFields
     */
    public function shouldAllowGetPreviouslySetField($fieldName, $getter, $setter)
    {
        $details = new AgreementDetails;

        $details->$setter('theValue');
        
        $this->assertEquals('theValue', $details->$getter());
    }

    /**
     * @test
     *
     * @dataProvider provideFields
     */
    public function shouldAllowAccessAsArrayPreviouslySetField($fieldName, $getter, $setter)
    {
        $details = new AgreementDetails;

        $details->$setter('theValue');

        $this->assertEquals('theValue', $details[$fieldName]);
    }

    /**
     * @test
     *
     * @dataProvider provideFields
     */
    public function shouldAllowSetAsArrayField($fieldName, $getter, $setter)
    {
        $details = new AgreementDetails;

        $details[$fieldName] = 'theValue';

        $this->assertEquals('theValue', $details[$fieldName]);
    }

    /**
     * @test
     */
    public function shouldAllowIterateOverSetFields()
    {
        $details = new AgreementDetails;

        $details['agreementRef'] = 'foo';
        $details->setDescription('baz');

        $this->assertEquals(
            array('agreementRef' => 'foo', 'description' => 'baz'), 
            iterator_to_array($details)
        );
    }

    /**
     * @test
     */
    public function shouldFilterNullFieldsWhileIterationeOverFields()
    {
        $details = new AgreementDetails;

        $details->setAgreementRef('');
        $details->setDescription(null);

        $this->assertEquals(
            array('agreementRef' => ''),
            iterator_to_array($details)
        );
    }
}