<?php
namespace Payum\Paypal\ExpressCheckout\Nvp;

use Payum\InstructionInterface;

/**
 * Docs:
 *   SetExpressCheckout: {@link https://www.x.com/developers/paypal/documentation-tools/api/setexpresscheckout-api-operation-nvp}
 */
class Instruction implements InstructionInterface
{
    protected $token;

    protected $custom;

    protected $invnum;
    
    protected $phonenum;
    
    protected $paypaladjustment;
    
    protected $note;
    
    protected $redirectrequired;
    
    protected $checkoutstatus;
    
    protected $giftmessage;

    protected $giftreceiptenable;
    
    protected $giftwrapname;
    
    protected $giftwrapamount;
    
    protected $buyermarketingemail;
    
    protected $surveyquestion;
    
    protected $surveychoiceselected;
    
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
    
    protected $paymentrequest_n_shiptoname = array();
    
    protected $paymentrequest_n_shiptostreet = array();
    
    protected $paymentrequest_n_shiptostreet2 = array();
    
    protected $paymentrequest_n_shiptocity = array();
    
    protected $paymentrequest_n_shiptostate = array();
    
    protected $paymentrequest_n_shiptozip = array();
    
    protected $paymentrequest_n_shiptocountrycode = array();
    
    protected $paymentrequest_n_shiptophonenum = array();
    
    protected $paymentrequest_n_addressstatus = array();
    
    protected $paymentrequest_n_amt = array();
    
    protected $paymentrequest_n_currencycode = array();
    
    protected $paymentrequest_n_itemamt = array();
    
    protected $paymentrequest_n_shippingamt = array();
    
    protected $paymentrequest_n_insuranceamt = array();
    
    protected $paymentrequest_n_shipdiscamt = array();
    
    protected $paymenrequest_n_insuranceoptionoffered = array();
    
    protected $paymentrequest_n_handlingamt = array();
    
    protected $paymentrequest_n_taxamt = array();
    
    protected $paymentrequest_n_desc = array();
    
    protected $paymentrequest_n_custom = array();
    
    protected $paymentrequest_n_invnum = array();
    
    protected $paymentrequest_n_notifyurl = array();
    
    protected $paymentrequest_n_notetext = array();
    
    protected $paymentrequest_n_transactionid = array();
    
    protected $paymentrequest_n_allowedpaymentmethod = array();
    
    protected $paymentrequest_n_paymentrequestid = array();

    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getCustom()
    {
        return $this->custom;
    }

    public function setCustom($custom)
    {
        $this->custom = $custom;
    }

    public function getInvnum()
    {
        return $this->invnum;
    }

    public function setInvnum($invnum)
    {
        $this->invnum = $invnum;
    }

    public function getPhonenum()
    {
        return $this->phonenum;
    }

    public function setPhonenum($phonenum)
    {
        $this->phonenum = $phonenum;
    }

    public function getPaypaladjustment()
    {
        return $this->paypaladjustment;
    }

    public function setPaypaladjustment($paypaladjustment)
    {
        $this->paypaladjustment = $paypaladjustment;
    }

    public function getNote()
    {
        return $this->note;
    }

    public function setNote($note)
    {
        $this->note = $note;
    }

    public function getRedirectrequired()
    {
        return $this->redirectrequired;
    }

    public function setRedirectrequired($redirectrequired)
    {
        $this->redirectrequired = $redirectrequired;
    }

    public function getCheckoutstatus()
    {
        return $this->checkoutstatus;
    }

    public function setCheckoutstatus($checkoutstatus)
    {
        $this->checkoutstatus = $checkoutstatus;
    }

    public function getGiftmessage()
    {
        return $this->giftmessage;
    }

    public function setGiftmessage($giftmessage)
    {
        $this->giftmessage = $giftmessage;
    }

    public function getGiftreceiptenable()
    {
        return $this->giftreceiptenable;
    }

    public function setGiftreceiptenable($giftreceiptenable)
    {
        $this->giftreceiptenable = $giftreceiptenable;
    }

    public function getGiftwrapname()
    {
        return $this->giftwrapname;
    }

    public function setGiftwrapname($giftwrapname)
    {
        $this->giftwrapname = $giftwrapname;
    }

    public function getGiftwrapamount()
    {
        return $this->giftwrapamount;
    }

    public function setGiftwrapamount($giftwrapamount)
    {
        $this->giftwrapamount = $giftwrapamount;
    }

    public function getBuyermarketingemail()
    {
        return $this->buyermarketingemail;
    }

    public function setBuyermarketingemail($buyermarketingemail)
    {
        $this->buyermarketingemail = $buyermarketingemail;
    }

    public function getSurveyquestion()
    {
        return $this->surveyquestion;
    }

    public function setSurveyquestion($surveyquestion)
    {
        $this->surveyquestion = $surveyquestion;
    }

    public function getSurveychoiceselected()
    {
        return $this->surveychoiceselected;
    }

    public function setSurveychoiceselected($surveychoiceselected)
    {
        $this->surveychoiceselected = $surveychoiceselected;
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

    public function getPaymentrequestNShiptostreet($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNShiptostreet($n, $paymentrequest_n_shiptostreet)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_shiptostreet);
    }

    public function getPaymentrequestNShiptoname($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNShiptoname($paymentrequest_n_shiptoname)
    {
        $this->paymentrequest_n_shiptoname = $paymentrequest_n_shiptoname;
    }

    public function getPaymentrequestNShiptostreet2($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNShiptostreet2($paymentrequest_n_shiptostreet2)
    {
        $this->paymentrequest_n_shiptostreet2 = $paymentrequest_n_shiptostreet2;
    }

    public function getPaymentrequestNShiptocity($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNShiptocity($paymentrequest_n_shiptocity)
    {
        $this->paymentrequest_n_shiptocity = $paymentrequest_n_shiptocity;
    }
    
    public function getPaymentrequestNShiptostate($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNShiptostate($paymentrequest_n_shiptostate)
    {
        $this->paymentrequest_n_shiptostate = $paymentrequest_n_shiptostate;
    }

    public function getPaymentrequestNShiptozip($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNShiptozip($paymentrequest_n_shiptozip)
    {
        $this->paymentrequest_n_shiptozip = $paymentrequest_n_shiptozip;
    }

    public function getPaymentrequestNShiptocountrycode($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNShiptocountrycode($paymentrequest_n_shiptocountrycode)
    {
        $this->paymentrequest_n_shiptocountrycode = $paymentrequest_n_shiptocountrycode;
    }

    public function getPaymentrequestNShiptophonenum($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNShiptophonenum($paymentrequest_n_shiptophonenum)
    {
        $this->paymentrequest_n_shiptophonenum = $paymentrequest_n_shiptophonenum;
    }

    public function getPaymentrequestNAddressstatus($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNAddressstatus($paymentrequest_n_addressstatus)
    {
        $this->paymentrequest_n_addressstatus = $paymentrequest_n_addressstatus;
    }

    public function getPaymentrequestNAmt($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNAmt($paymentrequest_n_amt)
    {
        $this->paymentrequest_n_amt = $paymentrequest_n_amt;
    }

    public function getPaymentrequestNCurrencycode($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNCurrencycode($paymentrequest_n_currencycode)
    {
        $this->paymentrequest_n_currencycode = $paymentrequest_n_currencycode;
    }

    public function getPaymentrequestNItemamt($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNItemamt($paymentrequest_n_itemamt)
    {
        $this->paymentrequest_n_itemamt = $paymentrequest_n_itemamt;
    }

    public function getPaymentrequestNShippingamt($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNShippingamt($paymentrequest_n_shippingamt)
    {
        $this->paymentrequest_n_shippingamt = $paymentrequest_n_shippingamt;
    }

    public function getPaymentrequestNInsuranceamt($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNInsuranceamt($paymentrequest_n_insuranceamt)
    {
        $this->paymentrequest_n_insuranceamt = $paymentrequest_n_insuranceamt;
    }

    public function getPaymentrequestNShipdiscamt($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNShipdiscamt($paymentrequest_n_shipdiscamt)
    {
        $this->paymentrequest_n_shipdiscamt = $paymentrequest_n_shipdiscamt;
    }

    public function getPaymenrequestNInsuranceoptionoffered($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymenrequestNInsuranceoptionoffered($paymenrequest_n_insuranceoptionoffered)
    {
        $this->paymenrequest_n_insuranceoptionoffered = $paymenrequest_n_insuranceoptionoffered;
    }

    public function getPaymentrequestNHandlingamt($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNHandlingamt($paymentrequest_n_handlingamt)
    {
        $this->paymentrequest_n_handlingamt = $paymentrequest_n_handlingamt;
    }

    public function getPaymentrequestNTaxamt($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNTaxamt($paymentrequest_n_taxamt)
    {
        $this->paymentrequest_n_taxamt = $paymentrequest_n_taxamt;
    }

    public function getPaymentrequestNDesc($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNDesc($paymentrequest_n_desc)
    {
        $this->paymentrequest_n_desc = $paymentrequest_n_desc;
    }

    public function getPaymentrequestNCustom($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNCustom($paymentrequest_n_custom)
    {
        $this->paymentrequest_n_custom = $paymentrequest_n_custom;
    }

    public function getPaymentrequestNInvnum($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNInvnum($paymentrequest_n_invnum)
    {
        $this->paymentrequest_n_invnum = $paymentrequest_n_invnum;
    }

    public function getPaymentrequestNNotifyurl($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNNotifyurl($paymentrequest_n_notifyurl)
    {
        $this->paymentrequest_n_notifyurl = $paymentrequest_n_notifyurl;
    }

    public function getPaymentrequestNNotetext($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNNotetext($paymentrequest_n_notetext)
    {
        $this->paymentrequest_n_notetext = $paymentrequest_n_notetext;
    }

    public function getPaymentrequestNTransactionid($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNTransactionid($paymentrequest_n_transactionid)
    {
        $this->paymentrequest_n_transactionid = $paymentrequest_n_transactionid;
    }

    public function getPaymentrequestNAllowedpaymentmethod($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNAllowedpaymentmethod($paymentrequest_n_allowedpaymentmethod)
    {
        $this->paymentrequest_n_allowedpaymentmethod = $paymentrequest_n_allowedpaymentmethod;
    }

    public function getPaymentrequestNPaymentrequestid($n)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNPaymentrequestid($paymentrequest_n_paymentrequestid)
    {
        $this->paymentrequest_n_paymentrequestid = $paymentrequest_n_paymentrequestid;
    }
    
    protected function getPaymentrequest($method, $n)
    {
        $property = strtolower(str_replace('getPaymentrequestN', 'paymentrequest_n_', $method));
        
        return isset($this->$property[$n]) ? $this->$property[$n] : null;  
    }

    protected function setPaymentrequest($method, $n, $value)
    {
        $property = strtolower(str_replace('setPaymentrequestN', 'paymentrequest_n_', $method));

        $this->$property[$n] = $value;
    }
}