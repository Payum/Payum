<?php
namespace Payum\Paypal\ProCheckout\Nvp;

use Payum\PaymentInstructionInterface;
use Payum\Exception\InvalidArgumentException;

/**
 * @see https://www.x.com/sites/default/files/payflowgateway_guide.pdf
 */
class PaymentInstruction implements PaymentInstructionInterface
{
    /**
     * @var array
     */
    protected $request = array(
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
    );

    protected $response = array(
        'pnref' => null,
        'ppref' => null,
        'result' => null,
        'cvv2match' => null,
        'respmsg' => null,
        'prefpsmsg' => null,
        'postfpsmsg' => null,
        'authcode' => null,
        'avsaddr' => null,
        'avszip' => null,
        'iavs' => null,
        'procavs' => null,
        'proccvv2' => null,
        'hostcode' => null,
        'resptext' => null,
        'proccardsecure' => null,
        'addlmsgs' => null,
        'paymenttype' => null,
        'correlationid' => null,
        'amexid' => null,
        'amexposdata' => null,
        'amt' => null,
        'origamt' => null,
        'cardtype' => null,
        'emailmatch' => null,
        'phonematch' => null,
        'extrspmsg' => null,
        'transtime' => null,
        'duplicate' => null,
        'date_to_settle' => null,
    );

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->request['CURRENCY'];
    }

    /**
     * @param string $currency
     *
     * @return PaymentInstruction
     */
    public function setCurrency($currency)
    {
        $this->request['CURRENCY'] = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getAmt()
    {
        return $this->request['AMT'];
    }

    /**
     * @param string $amt
     *
     * @return PaymentInstruction
     */
    public function setAmt($amt)
    {
        $this->request['AMT'] = $amt;
        return $this;
    }

    /**
     * @return string
     */
    public function getAcct()
    {
        return $this->request['ACCT'];
    }

    /**
     * @param string $acct
     *
     * @return PaymentInstruction
     */
    public function setAcct($acct)
    {
        $this->request['ACCT'] = $acct;
        return $this;
    }

    /**
     * @return string
     */
    public function getExpDate()
    {
        return $this->request['EXPDATE'];
    }

    /**
     * @param string $expDate
     *
     * @return PaymentInstruction
     */
    public function setExpDate($expDate)
    {
        $this->request['EXPDATE'] = $expDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getCvv2()
    {
        return $this->request['CVV2'];
    }

    /**
     * @param string $cvv2
     *
     * @return PaymentInstruction
     */
    public function setCvv2($cvv2)
    {
        $this->request['CVV2'] = $cvv2;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillToFirstName()
    {
        return $this->request['BILLTOFIRSTNAME'];
    }

    /**
     * @param string $billToFirstName
     *
     * @return PaymentInstruction
     */
    public function setBillToFirstName($billToFirstName)
    {
        $this->request['BILLTOFIRSTNAME'] = $billToFirstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillToLastName()
    {
        return $this->request['BILLTOLASTNAME'];
    }

    /**
     * @param string $billToLastName
     *
     * @return PaymentInstruction
     */
    public function setBillToLastName($billToLastName)
    {
        $this->request['BILLTOLASTNAME'] = $billToLastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillToStreet()
    {
        return $this->request['BILLTOSTREET'];
    }

    /**
     * @param string $billToLastStreet
     *
     * @return PaymentInstruction
     */
    public function setBillToStreet($billToLastStreet)
    {
        $this->request['BILLTOSTREET'] = $billToLastStreet;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillToCity()
    {
        return $this->request['BILLTOCITY'];
    }

    /**
     * @param string $billToLastCity
     *
     * @return PaymentInstruction
     */
    public function setBillToCity($billToLastCity)
    {
        $this->request['BILLTOCITY'] = $billToLastCity;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillToState()
    {
        return $this->request['BILLTOSTATE'];
    }

    /**
     * @param string $billToLastState
     *
     * @return PaymentInstruction
     */
    public function setBillToState($billToLastState)
    {
        $this->request['BILLTOSTATE'] = $billToLastState;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillToZip()
    {
        return $this->request['BILLTOZIP'];
    }

    /**
     * @param string $billToLastZip
     *
     * @return PaymentInstruction
     */
    public function setBillToZip($billToLastZip)
    {
        $this->request['BILLTOZIP'] = $billToLastZip;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillToCountr()
    {
        return $this->request['BILLTOCOUNTRY'];
    }

    /**
     * @param string $billToLastCountr
     *
     * @return PaymentInstruction
     */
    public function setBillToCountr($billToLastCountr)
    {
        $this->request['BILLTOCOUNTRY'] = $billToLastCountr;
        return $this;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param $nvp array|\Traversable 
     */
    public function fromNvp($nvp)
    {
        if (false == (is_array($nvp) || $nvp instanceof \Traversable)) {
            throw new InvalidArgumentException('Invalid nvp argument. Should be an array of an object implemented Traversable interface.');
        }
        foreach ($nvp as $name => $value) {
            $name = strtolower($name);
            if (!array_key_exists($name, $this->response)) {
                trigger_error(
                  "Key '{$name}' does not exist in the repose: " . print_r($this->response, true),
                  E_USER_NOTICE
                );
            }
            $this->response[$name] = $value;
        } 
    }
    
    public function toNvp()
    {
        $nvp = array();
        foreach ($this->request as $name => $value) {
            $nvp[$name] = $value;
        }

        return array_filter($nvp);
    }
}
