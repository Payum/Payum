<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Model;

/**
 * @link https://www.x.com/developers/paypal/documentation-tools/api/createrecurringpaymentsprofile-api-operation-nvp
 */
class RecurringPaymentDetails extends BaseModel
{
    protected $token;

    protected $status;
    
    protected $subscribername;
    
    protected $profilestartdate;
    
    protected $profilereference;
    
    protected $desc;
    
    protected $maxfailedpayments;
    
    protected $autobilloutamt;
    
    protected $billingperiod;
    
    protected $billingfrequency;
    
    protected $totalbillingcycles;
    
    protected $amt;
    
    protected $trialbillingperiod;
    
    protected $trialbillingfrequency;
    
    protected $trialtotalbillingcycles;
    
    protected $trialamt;
    
    protected $currencycode;
    
    protected $shippingamt;
    
    protected $taxamt;
    
    protected $initamt;
    
    protected $failedinitamtaction;
    
    protected $shiptoname;
    
    protected $shiptostreet;
    
    protected $shiptostreet2;
    
    protected $shiptocity;
    
    protected $shiptostate;
    
    protected $shiptozip;
    
    protected $shiptocountry;
    
    protected $shiptophonenum;
    
    protected $creditcardtype;
    
    protected $acct;
    
    protected $expdate;
    
    protected $cvv2;
    
    protected $startdate;
    
    protected $issuenumber;
    
    protected $email;
    
    protected $payerid;
    
    protected $payerstatus;
    
    protected $countrycode;
    
    protected $business;
    
    protected $salutation;
    
    protected $firstname;

    protected $middlename;
    
    protected $lastname;
    
    protected $suffix;
    
    protected $street;
    
    protected $street2;
    
    protected $city;
    
    protected $state;
    
    protected $zip;

    protected $profileid;

    protected $profilestatus;

    protected $timestamp;

    protected $correlationid;

    protected $version;

    protected $build;

    protected $ack;

    protected $aggregateamount;
    
    protected $aggregateoptionalamount;
    
    protected $finalpaymentduedate;
    
    protected $addressstatus;

    protected $regularbillingperiod;
    
    protected $regularbillingfrequency;

    protected $regulartotalbillingcycles;
    
    protected $regularamt;
    
    protected $regularshippingamt;

    protected $regulartaxamt;
    
    protected $regularcurrencycode;
    
    protected $nextbillingdate;
    
    protected $numcylescompleted;
    
    protected $numcyclesremaining;

    protected $outstandingbalance;
    
    protected $failedpaymentcount;

    protected $lastpaymentdate;

    protected $lastpaymentamt;
    
    protected $action;
    
    protected $note;
    
    protected $l_paymentrequest_nnn_itemcategorymmm = array();
    
    protected $l_paymentrequest_nnn_namemmm = array();

    protected $l_paymentrequest_nnn_descmmm = array();

    protected $l_paymentrequest_nnn_amtmmm = array();
    
    protected $l_paymentrequest_nnn_numbermmm = array();
    
    protected $l_paymentrequest_nnn_qtymmm = array();

    protected $l_paymentrequest_nnn_taxamtmmm = array();

    protected $l_errorcodennn = array();

    protected $l_shortmessagennn = array();

    protected $l_longmessagennn = array();

    protected $l_severitycodennn = array();

    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getSubscribername()
    {
        return $this->subscribername;
    }

    public function setSubscribername($subscribername)
    {
        $this->subscribername = $subscribername;
    }

    public function getProfilestartdate()
    {
        return $this->profilestartdate;
    }

    public function setProfilestartdate($profilestartdate)
    {
        $this->profilestartdate = $profilestartdate;
    }

    public function getProfilereference()
    {
        return $this->profilereference;
    }

    public function setProfilereference($profilereference)
    {
        $this->profilereference = $profilereference;
    }

    public function getDesc()
    {
        return $this->desc;
    }

    public function setDesc($desc)
    {
        $this->desc = $desc;
    }

    public function getMaxfailedpayments()
    {
        return $this->maxfailedpayments;
    }

    public function setMaxfailedpayments($maxfailedpayments)
    {
        $this->maxfailedpayments = $maxfailedpayments;
    }

    public function getAutobilloutamt()
    {
        return $this->autobilloutamt;
    }

    public function setAutobilloutamt($autobilloutamt)
    {
        $this->autobilloutamt = $autobilloutamt;
    }

    public function getBillingperiod()
    {
        return $this->billingperiod;
    }

    public function setBillingperiod($billingperiod)
    {
        $this->billingperiod = $billingperiod;
    }

    public function getBillingfrequency()
    {
        return $this->billingfrequency;
    }

    public function setBillingfrequency($billingfrequency)
    {
        $this->billingfrequency = $billingfrequency;
    }

    public function getTotalbillingcycles()
    {
        return $this->totalbillingcycles;
    }

    public function setTotalbillingcycles($totalbillingcycles)
    {
        $this->totalbillingcycles = $totalbillingcycles;
    }

    public function getAmt()
    {
        return $this->amt;
    }

    public function setAmt($amt)
    {
        $this->amt = $amt;
    }

    public function getTrialbillingperiod()
    {
        return $this->trialbillingperiod;
    }

    public function setTrialbillingperiod($trialbillingperiod)
    {
        $this->trialbillingperiod = $trialbillingperiod;
    }

    public function getTrialbillingfrequency()
    {
        return $this->trialbillingfrequency;
    }

    public function setTrialbillingfrequency($trialbillingfrequency)
    {
        $this->trialbillingfrequency = $trialbillingfrequency;
    }

    public function getTrialtotalbillingcycles()
    {
        return $this->trialtotalbillingcycles;
    }

    public function setTrialtotalbillingcycles($trialtotalbillingcycles)
    {
        $this->trialtotalbillingcycles = $trialtotalbillingcycles;
    }

    public function getTrialamt()
    {
        return $this->trialamt;
    }

    public function setTrialamt($trialamt)
    {
        $this->trialamt = $trialamt;
    }

    public function getCurrencycode()
    {
        return $this->currencycode;
    }

    public function setCurrencycode($currencycode)
    {
        $this->currencycode = $currencycode;
    }

    public function getShippingamt()
    {
        return $this->shippingamt;
    }

    public function setShippingamt($shippingamt)
    {
        $this->shippingamt = $shippingamt;
    }

    public function getTaxamt()
    {
        return $this->taxamt;
    }

    public function setTaxamt($taxamt)
    {
        $this->taxamt = $taxamt;
    }

    public function getInitamt()
    {
        return $this->initamt;
    }

    public function setInitamt($initamt)
    {
        $this->initamt = $initamt;
    }

    public function setFailedinitamtaction($failedinitamtaction)
    {
        $this->failedinitamtaction = $failedinitamtaction;
    }

    public function getFailedinitamtaction()
    {
        return $this->failedinitamtaction;
    }

    public function getShiptoname()
    {
        return $this->shiptoname;
    }

    public function setShiptoname($shiptoname)
    {
        $this->shiptoname = $shiptoname;
    }

    public function getShiptostreet()
    {
        return $this->shiptostreet;
    }

    public function setShiptostreet($shiptostreet)
    {
        $this->shiptostreet = $shiptostreet;
    }

    public function getShiptostreet2()
    {
        return $this->shiptostreet2;
    }

    public function setShiptostreet2($shiptostreet2)
    {
        $this->shiptostreet2 = $shiptostreet2;
    }

    public function getShiptocity()
    {
        return $this->shiptocity;
    }

    public function setShiptocity($shiptocity)
    {
        $this->shiptocity = $shiptocity;
    }

    public function getShiptostate()
    {
        return $this->shiptostate;
    }

    public function setShiptostate($shiptostate)
    {
        $this->shiptostate = $shiptostate;
    }

    public function getShiptozip()
    {
        return $this->shiptozip;
    }

    public function setShiptozip($shiptozip)
    {
        $this->shiptozip = $shiptozip;
    }

    public function getShiptocountry()
    {
        return $this->shiptocountry;
    }

    public function setShiptocountry($shiptocountry)
    {
        $this->shiptocountry = $shiptocountry;
    }

    public function getShiptophonenum()
    {
        return $this->shiptophonenum;
    }

    public function setShiptophonenum($shiptophonenum)
    {
        $this->shiptophonenum = $shiptophonenum;
    }

    public function getCreditcardtype()
    {
        return $this->creditcardtype;
    }

    public function setCreditcardtype($creditcardtype)
    {
        $this->creditcardtype = $creditcardtype;
    }

    public function getAcct()
    {
        return $this->acct;
    }

    public function setAcct($acct)
    {
        $this->acct = $acct;
    }

    public function getExpdate()
    {
        return $this->expdate;
    }

    public function setExpdate($expdate)
    {
        $this->expdate = $expdate;
    }

    public function getCvv2()
    {
        return $this->cvv2;
    }

    public function setCvv2($cvv2)
    {
        $this->cvv2 = $cvv2;
    }

    public function getStartdate()
    {
        return $this->startdate;
    }

    public function setStartdate($startdate)
    {
        $this->startdate = $startdate;
    }

    public function getIssuenumber()
    {
        return $this->issuenumber;
    }

    public function setIssuenumber($issuenumber)
    {
        $this->issuenumber = $issuenumber;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getPayerid()
    {
        return $this->payerid;
    }

    public function setPayerid($payerid)
    {
        $this->payerid = $payerid;
    }

    public function getPayerstatus()
    {
        return $this->payerstatus;
    }

    public function setPayerstatus($payerstatus)
    {
        $this->payerstatus = $payerstatus;
    }

    public function getCountrycode()
    {
        return $this->countrycode;
    }

    public function setCountrycode($countrycode)
    {
        $this->countrycode = $countrycode;
    }

    public function getBusiness()
    {
        return $this->business;
    }

    public function setBusiness($business)
    {
        $this->business = $business;
    }

    public function getSalutation()
    {
        return $this->salutation;
    }

    public function setSalutation($salutation)
    {
        $this->salutation = $salutation;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    public function getMiddlename()
    {
        return $this->middlename;
    }

    public function setMiddlename($middlename)
    {
        $this->middlename = $middlename;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }

    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function setStreet($street)
    {
        $this->street = $street;
    }

    public function getStreet2()
    {
        return $this->street2;
    }

    public function setStreet2($street2)
    {
        $this->street2 = $street2;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function getZip()
    {
        return $this->zip;
    }

    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    public function getProfileid()
    {
        return $this->profileid;
    }

    public function setProfileid($profileid)
    {
        $this->profileid = $profileid;
    }

    public function getProfilestatus()
    {
        return $this->profilestatus;
    }

    public function setProfilestatus($profilestatus)
    {
        $this->profilestatus = $profilestatus;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    public function getCorrelationid()
    {
        return $this->correlationid;
    }

    public function setCorrelationid($correlationid)
    {
        $this->correlationid = $correlationid;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function getBuild()
    {
        return $this->build;
    }

    public function setBuild($build)
    {
        $this->build = $build;
    }

    public function getAck()
    {
        return $this->ack;
    }

    public function setAck($ack)
    {
        $this->ack = $ack;
    }

    public function getAggregateamount()
    {
        return $this->aggregateamount;
    }

    public function setAggregateamount($aggregateamount)
    {
        $this->aggregateamount = $aggregateamount;
    }

    public function getAggregateoptionalamount()
    {
        return $this->aggregateoptionalamount;
    }

    public function setAggregateoptionalamount($aggregateoptionalamount)
    {
        $this->aggregateoptionalamount = $aggregateoptionalamount;
    }

    public function getFinalpaymentduedate()
    {
        return $this->finalpaymentduedate;
    }

    public function setFinalpaymentduedate($finalpaymentduedate)
    {
        $this->finalpaymentduedate = $finalpaymentduedate;
    }

    public function getAddressstatus()
    {
        return $this->addressstatus;
    }

    public function setAddressstatus($addressstatus)
    {
        $this->addressstatus = $addressstatus;
    }

    public function getRegularbillingperiod()
    {
        return $this->regularbillingperiod;
    }

    public function setRegularbillingperiod($regularbillingperiod)
    {
        $this->regularbillingperiod = $regularbillingperiod;
    }

    public function getRegularbillingfrequency()
    {
        return $this->regularbillingfrequency;
    }

    public function setRegularbillingfrequency($regularbillingfrequency)
    {
        $this->regularbillingfrequency = $regularbillingfrequency;
    }

    public function getRegulartotalbillingcycles()
    {
        return $this->regulartotalbillingcycles;
    }

    public function setRegulartotalbillingcycles($regulartotalbillingcycles)
    {
        $this->regulartotalbillingcycles = $regulartotalbillingcycles;
    }

    public function getRegularamt()
    {
        return $this->regularamt;
    }

    public function setRegularamt($regularamt)
    {
        $this->regularamt = $regularamt;
    }

    public function getRegularshippingamt()
    {
        return $this->regularshippingamt;
    }

    public function setRegularshippingamt($regularshippingamt)
    {
        $this->regularshippingamt = $regularshippingamt;
    }

    public function getRegulartaxamt()
    {
        return $this->regulartaxamt;
    }

    public function setRegulartaxamt($regulartaxamt)
    {
        $this->regulartaxamt = $regulartaxamt;
    }

    public function getRegularcurrencycode()
    {
        return $this->regularcurrencycode;
    }

    public function setRegularcurrencycode($regularcurrencycode)
    {
        $this->regularcurrencycode = $regularcurrencycode;
    }

    public function getNextbillingdate()
    {
        return $this->nextbillingdate;
    }

    public function setNextbillingdate($nextbillingdate)
    {
        $this->nextbillingdate = $nextbillingdate;
    }

    public function getNumcylescompleted()
    {
        return $this->numcylescompleted;
    }

    public function setNumcylescompleted($numcylescompleted)
    {
        $this->numcylescompleted = $numcylescompleted;
    }

    public function getNumcyclesremaining()
    {
        return $this->numcyclesremaining;
    }

    public function setNumcyclesremaining($numcyclesremaining)
    {
        $this->numcyclesremaining = $numcyclesremaining;
    }

    public function getOutstandingbalance()
    {
        return $this->outstandingbalance;
    }

    public function setOutstandingbalance($outstandingbalance)
    {
        $this->outstandingbalance = $outstandingbalance;
    }

    public function getFailedpaymentcount()
    {
        return $this->failedpaymentcount;
    }

    public function setFailedpaymentcount($failedpaymentcount)
    {
        $this->failedpaymentcount = $failedpaymentcount;
    }

    public function getLastpaymentdate()
    {
        return $this->lastpaymentdate;
    }

    public function setLastpaymentdate($lastpaymentdate)
    {
        $this->lastpaymentdate = $lastpaymentdate;
    }

    public function getLastpaymentamt()
    {
        return $this->lastpaymentamt;
    }

    public function setLastpaymentamt($lastpaymentamt)
    {
        $this->lastpaymentamt = $lastpaymentamt;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getNote()
    {
        return $this->note;
    }

    public function setNote($note)
    {
        $this->note = $note;
    }

    public function getLPaymentrequestItemcategory($n, $m)
    {
        return $this->get('l_paymentrequest_nnn_itemcategorymmm', $n, $m);
    }

    public function setLPaymentrequestItemcategory($n, $m, $value)
    {
        $this->set('l_paymentrequest_nnn_itemcategorymmm', $value, $n, $m);
    }

    public function getLPaymentrequestName($n, $m)
    {
        return $this->get('l_paymentrequest_nnn_namemmm', $n, $m);
    }

    public function setLPaymentrequestName($n, $m, $value)
    {
        $this->set('l_paymentrequest_nnn_namemmm', $value, $n, $m);
    }

    public function getLPaymentrequestDesc($n, $m)
    {
        return $this->get('l_paymentrequest_nnn_descmmm', $n, $m);
    }

    public function setLPaymentrequestDesc($n, $m, $value)
    {
        $this->set('l_paymentrequest_nnn_descmmm', $value, $n, $m);
    }

    public function getLPaymentrequestAmt($n, $m)
    {
        return $this->get('l_paymentrequest_nnn_amtmmm', $n, $m);
    }

    public function setLPaymentrequestAmt($n, $m, $value)
    {
        $this->set('l_paymentrequest_nnn_amtmmm', $value, $n, $m);
    }

    public function getLPaymentrequestNumber($n, $m)
    {
        return $this->get('l_paymentrequest_nnn_numbermmm', $n, $m);
    }

    public function setLPaymentrequestNumber($n, $m, $value)
    {
        $this->set('l_paymentrequest_nnn_numbermmm', $value, $n, $m);
    }

    public function getLPaymentrequestQty($n, $m)
    {
        return $this->get('l_paymentrequest_nnn_qtymmm', $n, $m);
    }

    public function setLPaymentrequestQty($n, $m, $value)
    {
        $this->set('l_paymentrequest_nnn_qtymmm', $value, $n, $m);
    }

    public function getLPaymentrequestTaxamt($n, $m)
    {
        return $this->get('l_paymentrequest_nnn_taxamtmmm', $n, $m);
    }

    public function setLPaymentrequestTaxamt($n, $m, $value)
    {
        $this->set('l_paymentrequest_nnn_taxamtmmm', $value, $n, $m);
    }

    public function getLSeveritycoden($n = null)
    {
        return $this->get('l_severitycodennn', $n);
    }

    public function setLSeveritycoden($n, $value)
    {
        $this->set('l_severitycodennn', $value, $n);
    }

    public function getLLongmessagen($n = null)
    {
        return $this->get('l_longmessagennn', $n);
    }

    public function setLLongmessagen($n, $value)
    {
        $this->set('l_longmessagennn', $value, $n);
    }

    public function getLShortmessagen($n = null)
    {
        return $this->get('l_shortmessagennn', $n);
    }

    public function setLShortmessagen($n, $value)
    {
        $this->set('l_shortmessagennn', $value, $n);
    }

    public function getLErrorcoden($n = null)
    {
        return $this->get('l_errorcodennn', $n);
    }

    public function setLErrorcoden($n, $value)
    {
        $this->set('l_errorcodennn', $value, $n);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedToNvpProperties()
    {
        $rc = new \ReflectionClass(__CLASS__);

        $fields = array();
        foreach ($rc->getProperties() as $rp) {
            $fields[] = $rp->getName();
        }

        return $fields;
    }
}