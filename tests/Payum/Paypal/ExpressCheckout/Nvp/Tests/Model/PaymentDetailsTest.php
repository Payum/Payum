<?php
namespace Payum\Paypal\ProCheckout\Nvp\Tests\Model;

use Payum\Paypal\ProCheckout\Nvp\Model\PaymentDetails;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class PaymentDetailsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getRequest()
    {
        $obj = new PaymentDetails();
        $this->assertEquals(
            array(
                'CURRENCY' => null,
                'AMT' => null,
                'ACCT' => null,
                'EXPDATE' => null,
                'CVV2' => null,
                'BILLTOFIRSTNAME' => null,
                'BILLTOLASTNAME' => null,
                'BILLTOSTREET' => null,
                'BILLTOCITY' => null,
                'BILLTOSTATE' => null,
                'BILLTOZIP' => null,
                'BILLTOCOUNTRY' => null,
            ),
            $obj->getRequest()
        );
    }

    /**
     * @test
     * @depends getRequest
     */
    public function toNvp()
    {
        $obj = new PaymentDetails();
        $obj->setAcct('dsfg');
        $obj->setCurrency('USD');
        $this->assertEquals(
            array(
                'CURRENCY' => 'USD',
                'ACCT' => 'dsfg'
            ),
            $obj->toNvp()
        );
    }

    /**
     * @test
     */
    public function getResponse()
    {
        $obj = new PaymentDetails();
        $this->assertEquals(
            array(
                'PNREF' => null,
                'PPREF' => null,
                'RESULT' => null,
                'CVV2MATCH' => null,
                'RESPMSG' => null,
                'PREFPSMSG' => null,
                'POSTFPSMSG' => null,
                'AUTHCODE' => null,
                'AVSADDR' => null,
                'AVSZIP' => null,
                'IAVS' => null,
                'PROCAVS' => null,
                'PROCCVV2' => null,
                'HOSTCODE' => null,
                'RESPTEXT' => null,
                'PROCCARDSECURE' => null,
                'ADDLMSGS' => null,
                'PAYMENTTYPE' => null,
                'CORRELATIONID' => null,
                'AMEXID' => null,
                'AMEXPOSDATA' => null,
                'ORIGAMT' => null,
                'CARDTYPE' => null,
                'EMAILMATCH' => null,
                'PHONEMATCH' => null,
                'EXTRSPMSG' => null,
                'TRANSTIME' => null,
                'DUPLICATE' => null,
                'DATE_TO_SETTLE' => null,
            ),
            $obj->getResponse()
        );
    }

    /**
     * @test
     * @depends getResponse
     */
    public function fromNvp()
    {
        $obj = new PaymentDetails();
        $obj->fromNvp(array('CVV2MATCH' => '123'));
        $obj->fromNvp(array('DATE_TO_SETTLE' => '234'));
        $response = $obj->getResponse();
        $this->assertEquals('123', $response['CVV2MATCH']);
        $this->assertEquals('234', $response['DATE_TO_SETTLE']);
    }
}
