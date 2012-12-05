<?php
namespace Payum\Paypal\ExpressCheckout\Nvp;

use Payum\PaymentInstructionInterface;
use Payum\Exception\InvalidArgumentException;

/**
 * Docs:
 *   SetExpressCheckout: {@link https://www.x.com/developers/paypal/documentation-tools/api/setexpresscheckout-api-operation-nvp}
 */
class PaymentInstruction implements PaymentInstructionInterface
{
    protected $token = '';

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

    protected $timestamp;
    
    protected $correlationid;
    
    protected $version;
    
    protected $build;

    protected $ack;
    
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
    
    protected $paymentrequest_n_insuranceoptionoffered = array();
    
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
    
    protected $paymentrequest_n_paymentaction = array();
    
    protected $paymentrequest_n_paymentstatus = array();

    protected $paymentrequest_n_exchangerate = array();
    
    protected $paymentrequest_n_settleamt = array();
    
    protected $paymentrequest_n_feeamt = array();
    
    protected $paymentrequest_n_ordertime = array();
    
    protected $paymentrequest_n_paymenttype = array();
    
    protected $paymentrequest_n_transactiontype = array();
    
    protected $paymentrequest_n_receiptid = array();
    
    protected $paymentrequest_n_parenttransactionid = array();
    
    protected $paymentrequest_n_pendingreason = array();
    
    protected $paymentrequest_n_reasoncode = array();

    protected $l_errorcoden = array();

    protected $l_shortmessagen = array();
    
    protected $l_longmessagen = array();
    
    protected $l_severitycoden = array();

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

    public function getPaymentrequestNShiptostreet($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNShiptostreet($n, $paymentrequest_n_shiptostreet)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_shiptostreet);
    }

    public function getPaymentrequestNShiptoname($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNShiptoname($n, $paymentrequest_n_shiptoname)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_shiptoname);
    }

    public function getPaymentrequestNShiptostreet2($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNShiptostreet2($n, $paymentrequest_n_shiptostreet2)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_shiptostreet2);
    }

    public function getPaymentrequestNShiptocity($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNShiptocity($n, $paymentrequest_n_shiptocity)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_shiptocity);
    }
    
    public function getPaymentrequestNShiptostate($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNShiptostate($n, $paymentrequest_n_shiptostate)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_shiptostate);
    }

    public function getPaymentrequestNShiptozip($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNShiptozip($n, $paymentrequest_n_shiptozip)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_shiptozip);
    }
    
    public function getPaymentrequestNShiptocountrycode($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNShiptocountrycode($n, $paymentrequest_n_shiptocountrycode)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_shiptocountrycode);
    }

    public function getPaymentrequestNShiptophonenum($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNShiptophonenum($n, $paymentrequest_n_shiptophonenum)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_shiptophonenum);
    }

    public function getPaymentrequestNAddressstatus($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNAddressstatus($n, $paymentrequest_n_addressstatus)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_addressstatus);
    }

    public function getPaymentrequestNAmt($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNAmt($n, $paymentrequest_n_amt)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_amt);
    }

    public function getPaymentrequestNCurrencycode($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNCurrencycode($n, $paymentrequest_n_currencycode)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_currencycode);
    }

    public function getPaymentrequestNItemamt($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNItemamt($n, $paymentrequest_n_itemamt)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_itemamt);
    }

    public function getPaymentrequestNShippingamt($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNShippingamt($n, $paymentrequest_n_shippingamt)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_shippingamt);
    }

    public function getPaymentrequestNInsuranceamt($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNInsuranceamt($n, $paymentrequest_n_insuranceamt)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_insuranceamt);
    }

    public function getPaymentrequestNShipdiscamt($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNShipdiscamt($n, $paymentrequest_n_shipdiscamt)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_shipdiscamt);
    }

    public function getPaymentrequestNInsuranceoptionoffered($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNInsuranceoptionoffered($n, $paymentrequest_n_insuranceoptionoffered)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_insuranceoptionoffered);
    }

    public function getPaymentrequestNHandlingamt($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNHandlingamt($n, $paymentrequest_n_handlingamt)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_handlingamt);
    }

    public function getPaymentrequestNTaxamt($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNTaxamt($n, $paymentrequest_n_taxamt)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_taxamt);
    }
    
    public function getPaymentrequestNDesc($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNDesc($n, $paymentrequest_n_desc)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_desc);
    }

    public function getPaymentrequestNCustom($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNCustom($n, $paymentrequest_n_custom)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_custom);
    }

    public function getPaymentrequestNInvnum($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNInvnum($n, $paymentrequest_n_invnum)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_invnum);
    }

    public function getPaymentrequestNNotifyurl($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNNotifyurl($n, $paymentrequest_n_notifyurl)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_notifyurl);
    }

    public function getPaymentrequestNNotetext($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNNotetext($n, $paymentrequest_n_notetext)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_notetext);
    }

    public function getPaymentrequestNTransactionid($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNTransactionid($n, $paymentrequest_n_transactionid)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_transactionid);
    }

    public function getPaymentrequestNAllowedpaymentmethod($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNAllowedpaymentmethod($n, $paymentrequest_n_allowedpaymentmethod)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_allowedpaymentmethod);
    }

    public function getPaymentrequestNPaymentrequestid($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNPaymentrequestid($n, $paymentrequest_n_paymentrequestid)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_paymentrequestid);
    }
    
    public function getPaymentrequestNPaymentaction($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }
    
    public function setPaymentrequestNPaymentaction($n, $paymentrequest_n_paymentaction)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_paymentaction);
    }

    public function getPaymentrequestNPaymentstatus($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }
    
    public function setPaymentrequestNPaymentstatus($n, $paymentrequest_n_paymentstatus)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_paymentstatus);
    }

    public function getPaymentrequestNExchangerate($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNExchangerate($n, $paymentrequest_n_exchangerate)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_exchangerate);
    }

    public function getPaymentrequestNSettleamt($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNSettleamt($n, $paymentrequest_n_settleamt)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_settleamt);
    }

    public function getPaymentrequestNFeeamt($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNFeeamt($n, $paymentrequest_n_feeamt)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_feeamt);
    }

    public function getPaymentrequestNOrdertime($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNOrdertime($n, $paymentrequest_n_ordertime)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_ordertime);
    }

    public function getPaymentrequestNPaymenttype($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNPaymenttype($n, $paymentrequest_n_paymenttype)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_paymenttype);
    }

    public function getPaymentrequestNTransactiontype($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNTransactiontype($n, $paymentrequest_n_transactiontype)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_transactiontype);
    }

    public function getPaymentrequestNReceiptid($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNReceiptid($n, $paymentrequest_n_receiptid)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_receiptid);
    }

    public function getPaymentrequestNParenttransactionid($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNParenttransactionid($n, $paymentrequest_n_parenttransactionid)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_parenttransactionid);
    }

    public function getPaymentrequestNPendingreason($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNPendingreason($n, $paymentrequest_n_pendingreason)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_pendingreason);
    }

    public function getPaymentrequestNReasoncode($n = null)
    {
        return $this->getPaymentrequest(__METHOD__, $n);
    }

    public function setPaymentrequestNReasoncode($n, $paymentrequest_n_reasoncode)
    {
        $this->setPaymentrequest(__METHOD__, $n, $paymentrequest_n_reasoncode);
    }

    public function getLSeveritycoden($n = null)
    {
        if (null === $n) {
            return $this->l_severitycoden;
        }
        
        return isset($this->l_severitycoden[$n]) ? $this->l_severitycoden[$n] : null;
    }

    public function setLSeveritycoden($n, $l_severitycoden)
    {
        $this->l_severitycoden[$n] = $l_severitycoden;
    }

    public function getLLongmessagen($n = null)
    {
        if (null === $n) {
            return $this->l_longmessagen;
        }

        return isset($this->l_longmessagen[$n]) ? $this->l_longmessagen[$n] : null;
    }

    public function setLLongmessagen($n, $l_longmessagen)
    {
        $this->l_longmessagen[$n] = $l_longmessagen;
    }

    public function getLShortmessagen($n = null)
    {
        if (null === $n) {
            return $this->l_shortmessagen;
        }

        return isset($this->l_shortmessagen[$n]) ? $this->l_shortmessagen[$n] : null;
    }

    public function setLShortmessagen($n, $l_shortmessagen)
    {
        $this->l_shortmessagen[$n] = $l_shortmessagen;
    }

    public function getLErrorcoden($n = null)
    {
        if (null === $n) {
            return $this->l_errorcoden;
        }

        return isset($this->l_errorcoden[$n]) ? $this->l_errorcoden[$n] : null;
    }

    public function setLErrorcoden($n, $l_errorcoden)
    {
        $this->l_errorcoden[$n] = $l_errorcoden;
    }
    
    public function clearErrors()
    {
        $this->l_errorcoden = array();
        $this->l_longmessagen = array();
        $this->l_severitycoden = array();
        $this->l_shortmessagen = array();
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
            if (0 === strpos($name, 'PAYMENTREQUEST')) {
                list($part1, $part2, $part3) = explode('_', $name);
                
                $property = strtolower($part1.'_n_'.$part3);
                
                if (false == property_exists($this, $property)) {
                    continue;
                }
                
                $p = $this->$property;
                $p[$part2] = $value;
                $this->$property = $p;
                
                continue;
            }
            
            //14 symbols.
            if (0 === strpos($name, 'L_SEVERITYCODE') ||
                0 === strpos($name, 'L_SHORTMESSAGE') ||
                0 === strpos($name, 'L_LONGMESSAGE') ||
                0 === strpos($name, 'L_ERRORCODE')
            ) {
                $index = substr($name, -1);
                $property = substr(strtolower($name), 0, -1).'n';
                
                $p = $this->$property;
                $p[$index] = $value;
                $this->$property = $p;
            }
            
            $property = strtolower($name);
            if (false == property_exists($this, $property)) {
                continue;
            }
            
            $this->$property = $value;
        } 
    }
    
    public function toNvp()
    {
        $nvp = array();
        foreach (get_object_vars($this) as $property => $value) {            
            if (0 === strpos($property, 'paymentrequest')) {
                foreach ($value as $paymentrequestIndex => $paymentrequestValue) {
                    $name = strtoupper($property);
                    $name[15] = $paymentrequestIndex;
                    $nvp[$name] = $paymentrequestValue;
                }
                
                continue;
            }
            //14 symbols.
            if (in_array($property, array('l_severitycoden', 'l_shortmessagen', 'l_longmessagen', 'l_errorcoden'))) {
                foreach ($value as $paymentrequestIndex => $paymentrequestValue) {
                    $nvp[substr(strtoupper($property), 0, -1).$paymentrequestIndex] = $paymentrequestValue;
                }

                continue;
            }
            
            $nvp[strtoupper($property)] = $value;
        }
        
        return $nvp;
    }
    
    protected function getPaymentrequest($method, $n)
    {        
        list(, $method) = explode('::', $method);
        $property = strtolower(str_replace('getPaymentrequestN', 'paymentrequest_n_', $method));
        
        if (null === $n) {
            return $this->$property;
        }
        
        $p = $this->$property;
        
        return isset($p[$n]) ? $p[$n] : null;  
    }

    protected function setPaymentrequest($method, $n, $value)
    {
        list(, $method) = explode('::', $method);
        
        $property = strtolower(str_replace('setPaymentrequestN', 'paymentrequest_n_', $method));
        
        $p = $this->$property;
        $p[$n] =$value;
        $this->$property = $p;
    }
}