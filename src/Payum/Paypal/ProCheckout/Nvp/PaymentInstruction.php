<?php
namespace Payum\Paypal\ProCheckout\Nvp;

use Payum\Exception\InvalidArgumentException;

/**
 * @see https://www.x.com/sites/default/files/payflowgateway_guide.pdf
 */
class PaymentInstruction implements \ArrayAccess, \IteratorAggregate
{
    protected $request_currency;
    protected $request_amt;
    protected $request_acct;
    protected $request_expdate;
    protected $request_cvv2;
    protected $request_billtofirstname;
    protected $request_billtolastname;
    protected $request_billtostreet;
    protected $request_billtocity;
    protected $request_billtostate;
    protected $request_billtozip;
    protected $request_billtocountry;

    protected $response_pnref;
    protected $response_ppref;
    protected $response_result;
    protected $response_cvv2match;
    protected $response_respmsg;
    protected $response_prefpsmsg;
    protected $response_postfpsmsg;
    protected $response_authcode;
    protected $response_avsaddr;
    protected $response_avszip;
    protected $response_iavs;
    protected $response_procavs;
    protected $response_proccvv2;
    protected $response_hostcode;
    protected $response_resptext;
    protected $response_proccardsecure;
    protected $response_addlmsgs;
    protected $response_paymenttype;
    protected $response_correlationid;
    protected $response_amexid;
    protected $response_amexposdata;
    protected $response_amt;
    protected $response_origamt;
    protected $response_cardtype;
    protected $response_emailmatch;
    protected $response_phonematch;
    protected $response_extrspmsg;
    protected $response_transtime;
    protected $response_duplicate;
    protected $response_date_to_settle;

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->request_currency;
    }

    /**
     * @param string $currency
     *
     * @return PaymentInstruction
     */
    public function setCurrency($currency)
    {
        $this->request_currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getAmt()
    {
        return $this->request_amt;
    }

    /**
     * @param string $amt
     *
     * @return PaymentInstruction
     */
    public function setAmt($amt)
    {
        $this->request_amt = $amt;
        return $this;
    }

    /**
     * @return string
     */
    public function getAcct()
    {
        return $this->request_acct;
    }

    /**
     * @param string $acct
     *
     * @return PaymentInstruction
     */
    public function setAcct($acct)
    {
        $this->request_acct = $acct;
        return $this;
    }

    /**
     * @return string
     */
    public function getExpDate()
    {
        return $this->request_expdate;
    }

    /**
     * @param string $expDate
     *
     * @return PaymentInstruction
     */
    public function setExpDate($expDate)
    {
        $this->request_expdate = $expDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getCvv2()
    {
        return $this->request_cvv2;
    }

    /**
     * @param string $cvv2
     *
     * @return PaymentInstruction
     */
    public function setCvv2($cvv2)
    {
        $this->request_cvv2 = $cvv2;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillToFirstName()
    {
        return $this->request_billtofirstname;
    }

    /**
     * @param string $billToFirstName
     *
     * @return PaymentInstruction
     */
    public function setBillToFirstName($billToFirstName)
    {
        $this->request_billtofirstname = $billToFirstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillToLastName()
    {
        return $this->request_billtolastname;
    }

    /**
     * @param string $billToLastName
     *
     * @return PaymentInstruction
     */
    public function setBillToLastName($billToLastName)
    {
        $this->request_billtolastname = $billToLastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillToStreet()
    {
        return $this->request_billtostreet;
    }

    /**
     * @param string $billToLastStreet
     *
     * @return PaymentInstruction
     */
    public function setBillToStreet($billToLastStreet)
    {
        $this->request_billtostreet = $billToLastStreet;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillToCity()
    {
        return $this->request_billtocity;
    }

    /**
     * @param string $billToLastCity
     *
     * @return PaymentInstruction
     */
    public function setBillToCity($billToLastCity)
    {
        $this->request_billtocity = $billToLastCity;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillToState()
    {
        return $this->request_billtostate;
    }

    /**
     * @param string $billToLastState
     *
     * @return PaymentInstruction
     */
    public function setBillToState($billToLastState)
    {
        $this->request_billtostate = $billToLastState;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillToZip()
    {
        return $this->request_billtozip;
    }

    /**
     * @param string $billToLastZip
     *
     * @return PaymentInstruction
     */
    public function setBillToZip($billToLastZip)
    {
        $this->request_billtozip = $billToLastZip;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillToCountry()
    {
        return $this->request_billtocountry;
    }

    /**
     * @param string $billToLastCountr
     *
     * @return PaymentInstruction
     */
    public function setBillToCountry($billToLastCountry)
    {
        $this->request_billtocountry = $billToLastCountry;
        return $this;
    }

    /**
     * @param string $prefix
     *
     * @return array
     */
    protected function getProperties($prefix)
    {
        $properties = array();
        $reflection = new \ReflectionClass($this);
        /** @var $prop \ReflectionProperty */
        foreach ($reflection->getProperties() as $prop) {
            if ($prefix == substr($prop->getName(), 0, strlen($prefix))) {
                $name = $prop->getName();
                $properties[strtoupper(substr($prop->getName(), strlen($prefix)))] = $this->$name;
            }
        }

        return $properties;
    }

    /**
     * @return array
     */
    public function getRequest()
    {
        return $this->getProperties('request_');
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->getProperties('response_');
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
            $name = 'response_' . strtolower($name);
            if (!property_exists($this, $name)) {
                trigger_error(
                  "Key '{$name}' does not exist in the repose: " . print_r($this->getResponse(), true),
                  E_USER_NOTICE
                );
            }
            $this->$name = $value;
        } 
    }
    
    public function toNvp()
    {
        return array_filter($this->getRequest());
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->toNvp());
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->toNvp());
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        $nvp = $this->toNvp();

        return array_key_exists($offset, $nvp) ?
            $nvp[$offset] :
            null
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->fromNvp(array($offset => $value));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        throw new LogicException('Not implemented');
    }
}
