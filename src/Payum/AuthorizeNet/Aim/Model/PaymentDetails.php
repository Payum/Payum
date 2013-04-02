<?php
namespace Payum\AuthorizeNet\Aim\Model;

use Payum\Exception\InvalidArgumentException;

class PaymentDetails implements \ArrayAccess, \IteratorAggregate
{
    protected $address;
    
    protected $allow_partial_auth;
    
    protected $amount;
    
    protected $auth_code;
    
    protected $authentication_indicator;
    
    protected $bank_aba_code;
    
    protected $bank_acct_name;
    
    protected $bank_acct_num;
    
    protected $bank_acct_type;
    
    protected $bank_check_number;
    
    protected $bank_name;
    
    protected $card_code;
    
    protected $card_num;
    
    protected $cardholder_authentication_value;
    
    protected $city;
    
    protected $company;
    
    protected $country;
    
    protected $cust_id;
    
    protected $customer_ip;
    
    protected $delim_char;
    
    protected $delim_data;
    
    protected $description;
    
    protected $duplicate_window;
    
    protected $duty;
    
    protected $echeck_type;
    
    protected $email;
    
    protected $email_customer;
    
    protected $encap_char;
    
    protected $exp_date;
    
    protected $fax;
    
    protected $first_name;
    
    protected $footer_email_receipt;
    
    protected $freight;
    
    protected $header_email_receipt;
    
    protected $invoice_num;
    
    protected $last_name;
    
    protected $line_item;
    
    protected $login;
    
    protected $method;
    
    protected $phone;
    
    protected $po_num;
    
    protected $recurring_billing;
    
    protected $relay_response;
    
    protected $ship_to_address;
    
    protected $ship_to_city;
    
    protected $ship_to_company;
    
    protected $ship_to_country;
    
    protected $ship_to_first_name;
    
    protected $ship_to_last_name;
    
    protected $ship_to_state;
    
    protected $ship_to_zip;
    
    protected $split_tender_id;
    
    protected $state;
    
    protected $tax;
    
    protected $tax_exempt;
    
    protected $test_request;
    
    protected $tran_key;
    
    protected $trans_id;
    
    protected $type;
    
    protected $version;
    
    protected $zip;

    protected $error_message;

    protected $response_code;

    protected $response_subcode;
    
    protected $response_reason_code;
    
    protected $response_reason_text;
    
    protected $authorization_code;
    
    protected $avs_response;
    
    protected $transaction_id;
    
    protected $invoice_number;
    
    protected $transaction_type;
    
    protected $customer_id;
    
    protected $zip_code;
    
    protected $email_address;
    
    protected $ship_to_zip_code;
    
    protected $purchase_order_number;
    
    protected $md5_hash;
    
    protected $card_code_response;
    
    protected $cavv_response;
    
    protected $account_number;
    
    protected $card_type;
    
    protected $requested_amount;
    
    protected $balance_on_card;

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function getAllowPartialAuth()
    {
        return $this->allow_partial_auth;
    }

    public function setAllowPartialAuth($allow_partial_auth)
    {
        $this->allow_partial_auth = $allow_partial_auth;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function getAuthCode()
    {
        return $this->auth_code;
    }

    public function setAuthCode($auth_code)
    {
        $this->auth_code = $auth_code;
    }

    public function getAuthenticationIndicator()
    {
        return $this->authentication_indicator;
    }

    public function setAuthenticationIndicator($authentication_indicator)
    {
        $this->authentication_indicator = $authentication_indicator;
    }

    public function getBankAbaCode()
    {
        return $this->bank_aba_code;
    }

    public function setBankAbaCode($bank_aba_code)
    {
        $this->bank_aba_code = $bank_aba_code;
    }

    public function getBankAcctName()
    {
        return $this->bank_acct_name;
    }

    public function setBankAcctName($bank_acct_name)
    {
        $this->bank_acct_name = $bank_acct_name;
    }

    public function getBankAcctNum()
    {
        return $this->bank_acct_num;
    }

    public function setBankAcctNum($bank_acct_num)
    {
        $this->bank_acct_num = $bank_acct_num;
    }

    public function getBankAcctType()
    {
        return $this->bank_acct_type;
    }

    public function setBankAcctType($bank_acct_type)
    {
        $this->bank_acct_type = $bank_acct_type;
    }

    public function getBankCheckNumber()
    {
        return $this->bank_check_number;
    }

    public function setBankCheckNumber($bank_check_number)
    {
        $this->bank_check_number = $bank_check_number;
    }

    public function getBankName()
    {
        return $this->bank_name;
    }

    public function setBankName($bank_name)
    {
        $this->bank_name = $bank_name;
    }

    public function getCardCode()
    {
        return $this->card_code;
    }

    public function setCardCode($card_code)
    {
        $this->card_code = $card_code;
    }

    public function getCardNum()
    {
        return $this->card_num;
    }

    public function setCardNum($card_num)
    {
        $this->card_num = $card_num;
    }

    public function getCardholderAuthenticationValue()
    {
        return $this->cardholder_authentication_value;
    }

    public function setCardholderAuthenticationValue($cardholder_authentication_value)
    {
        $this->cardholder_authentication_value = $cardholder_authentication_value;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function getCompany()
    {
        return $this->company;
    }

    public function setCompany($company)
    {
        $this->company = $company;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function getCustId()
    {
        return $this->cust_id;
    }

    public function setCustId($cust_id)
    {
        $this->cust_id = $cust_id;
    }

    public function getCustomerIp()
    {
        return $this->customer_ip;
    }

    public function setCustomerIp($customer_ip)
    {
        $this->customer_ip = $customer_ip;
    }

    public function getDelimChar()
    {
        return $this->delim_char;
    }

    public function setDelimChar($delim_char)
    {
        $this->delim_char = $delim_char;
    }

    public function getDelimData()
    {
        return $this->delim_data;
    }

    public function setDelimData($delim_data)
    {
        $this->delim_data = $delim_data;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDuplicateWindow()
    {
        return $this->duplicate_window;
    }

    public function setDuplicateWindow($duplicate_window)
    {
        $this->duplicate_window = $duplicate_window;
    }

    public function getDuty()
    {
        return $this->duty;
    }

    public function setDuty($duty)
    {
        $this->duty = $duty;
    }

    public function getEcheckType()
    {
        return $this->echeck_type;
    }

    public function setEcheckType($echeck_type)
    {
        $this->echeck_type = $echeck_type;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmailCustomer()
    {
        return $this->email_customer;
    }

    public function setEmailCustomer($email_customer)
    {
        $this->email_customer = $email_customer;
    }

    public function getEncapChar()
    {
        return $this->encap_char;
    }

    public function setEncapChar($encap_char)
    {
        $this->encap_char = $encap_char;
    }

    public function getExpDate()
    {
        return $this->exp_date;
    }

    public function setExpDate($exp_date)
    {
        $this->exp_date = $exp_date;
    }

    public function getFax()
    {
        return $this->fax;
    }

    public function setFax($fax)
    {
        $this->fax = $fax;
    }

    public function getFirstName()
    {
        return $this->first_name;
    }

    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
    }

    public function getFooterEmailReceipt()
    {
        return $this->footer_email_receipt;
    }

    public function setFooterEmailReceipt($footer_email_receipt)
    {
        $this->footer_email_receipt = $footer_email_receipt;
    }

    public function getFreight()
    {
        return $this->freight;
    }

    public function setFreight($freight)
    {
        $this->freight = $freight;
    }

    public function getHeaderEmailReceipt()
    {
        return $this->header_email_receipt;
    }

    public function setHeaderEmailReceipt($header_email_receipt)
    {
        $this->header_email_receipt = $header_email_receipt;
    }

    public function getInvoiceNum()
    {
        return $this->invoice_num;
    }

    public function setInvoiceNum($invoice_num)
    {
        $this->invoice_num = $invoice_num;
    }

    public function getLastName()
    {
        return $this->last_name;
    }

    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
    }

    public function getLineItem()
    {
        return $this->line_item;
    }

    public function setLineItem($line_item)
    {
        $this->line_item = $line_item;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function setLogin($login)
    {
        $this->login = $login;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function getPoNum()
    {
        return $this->po_num;
    }

    public function setPoNum($po_num)
    {
        $this->po_num = $po_num;
    }

    public function getRecurringBilling()
    {
        return $this->recurring_billing;
    }

    public function setRecurringBilling($recurring_billing)
    {
        $this->recurring_billing = $recurring_billing;
    }

    public function getRelayResponse()
    {
        return $this->relay_response;
    }

    public function setRelayResponse($relay_response)
    {
        $this->relay_response = $relay_response;
    }

    public function getShipToAddress()
    {
        return $this->ship_to_address;
    }

    public function setShipToAddress($ship_to_address)
    {
        $this->ship_to_address = $ship_to_address;
    }

    public function getShipToCity()
    {
        return $this->ship_to_city;
    }

    public function setShipToCity($ship_to_city)
    {
        $this->ship_to_city = $ship_to_city;
    }

    public function getShipToCompany()
    {
        return $this->ship_to_company;
    }

    public function setShipToCompany($ship_to_company)
    {
        $this->ship_to_company = $ship_to_company;
    }

    public function getShipToCountry()
    {
        return $this->ship_to_country;
    }

    public function setShipToCountry($ship_to_country)
    {
        $this->ship_to_country = $ship_to_country;
    }

    public function getShipToFirstName()
    {
        return $this->ship_to_first_name;
    }

    public function setShipToFirstName($ship_to_first_name)
    {
        $this->ship_to_first_name = $ship_to_first_name;
    }

    public function getShipToLastName()
    {
        return $this->ship_to_last_name;
    }

    public function setShipToLastName($ship_to_last_name)
    {
        $this->ship_to_last_name = $ship_to_last_name;
    }

    public function getShipToState()
    {
        return $this->ship_to_state;
    }

    public function setShipToState($ship_to_state)
    {
        $this->ship_to_state = $ship_to_state;
    }

    public function getShipToZip()
    {
        return $this->ship_to_zip;
    }

    public function setShipToZip($ship_to_zip)
    {
        $this->ship_to_zip = $ship_to_zip;
    }

    public function getSplitTenderId()
    {
        return $this->split_tender_id;
    }

    public function setSplitTenderId($split_tender_id)
    {
        $this->split_tender_id = $split_tender_id;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function getTax()
    {
        return $this->tax;
    }

    public function setTax($tax)
    {
        $this->tax = $tax;
    }

    public function getTaxExempt()
    {
        return $this->tax_exempt;
    }

    public function setTaxExempt($tax_exempt)
    {
        $this->tax_exempt = $tax_exempt;
    }

    public function getTestRequest()
    {
        return $this->test_request;
    }

    public function setTestRequest($test_request)
    {
        $this->test_request = $test_request;
    }

    public function getTranKey()
    {
        return $this->tran_key;
    }

    public function setTranKey($tran_key)
    {
        $this->tran_key = $tran_key;
    }

    public function getTransId()
    {
        return $this->trans_id;
    }

    public function setTransId($trans_id)
    {
        $this->trans_id = $trans_id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function getZip()
    {
        return $this->zip;
    }

    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    public function getApproved()
    {
        return $this->approved;
    }

    public function setApproved($approved)
    {
        $this->approved = $approved;
    }

    public function getDeclined()
    {
        return $this->declined;
    }

    public function setDeclined($declined)
    {
        $this->declined = $declined;
    }

    public function getHeld()
    {
        return $this->held;
    }

    public function setHeld($held)
    {
        $this->held = $held;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setError($error)
    {
        $this->error = $error;
    }

    public function getErrorMessage()
    {
        return $this->error_message;
    }

    public function setErrorMessage($error_message)
    {
        $this->error_message = $error_message;
    }

    public function getResponseCode()
    {
        return $this->response_code;
    }

    public function setResponseCode($response_code)
    {
        $this->response_code = $response_code;
    }

    public function getResponseSubcode()
    {
        return $this->response_subcode;
    }

    public function setResponseSubcode($response_subcode)
    {
        $this->response_subcode = $response_subcode;
    }

    public function getResponseReasonCode()
    {
        return $this->response_reason_code;
    }

    public function setResponseReasonCode($response_reason_code)
    {
        $this->response_reason_code = $response_reason_code;
    }

    public function getResponseReasonText()
    {
        return $this->response_reason_text;
    }

    public function setResponseReasonText($response_reason_text)
    {
        $this->response_reason_text = $response_reason_text;
    }

    public function getAuthorizationCode()
    {
        return $this->authorization_code;
    }

    public function setAuthorizationCode($authorization_code)
    {
        $this->authorization_code = $authorization_code;
    }

    public function getAvsResponse()
    {
        return $this->avs_response;
    }

    public function setAvsResponse($avs_response)
    {
        $this->avs_response = $avs_response;
    }

    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    public function setTransactionId($transaction_id)
    {
        $this->transaction_id = $transaction_id;
    }

    public function getInvoiceNumber()
    {
        return $this->invoice_number;
    }

    public function setInvoiceNumber($invoice_number)
    {
        $this->invoice_number = $invoice_number;
    }

    public function getTransactionType()
    {
        return $this->transaction_type;
    }

    public function setTransactionType($transaction_type)
    {
        $this->transaction_type = $transaction_type;
    }

    public function getCustomerId()
    {
        return $this->customer_id;
    }

    public function setCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;
    }

    public function getZipCode()
    {
        return $this->zip_code;
    }

    public function setZipCode($zip_code)
    {
        $this->zip_code = $zip_code;
    }

    public function getEmailAddress()
    {
        return $this->email_address;
    }

    public function setEmailAddress($email_address)
    {
        $this->email_address = $email_address;
    }

    public function getShipToZipCode()
    {
        return $this->ship_to_zip_code;
    }

    public function setShipToZipCode($ship_to_zip_code)
    {
        $this->ship_to_zip_code = $ship_to_zip_code;
    }

    public function getPurchaseOrderNumber()
    {
        return $this->purchase_order_number;
    }

    public function setPurchaseOrderNumber($purchase_order_number)
    {
        $this->purchase_order_number = $purchase_order_number;
    }

    public function getMd5Hash()
    {
        return $this->md5_hash;
    }

    public function setMd5Hash($md5_hash)
    {
        $this->md5_hash = $md5_hash;
    }

    public function getCardCodeResponse()
    {
        return $this->card_code_response;
    }

    public function setCardCodeResponse($card_code_response)
    {
        $this->card_code_response = $card_code_response;
    }

    public function getCavvResponse()
    {
        return $this->cavv_response;
    }

    public function setCavvResponse($cavv_response)
    {
        $this->cavv_response = $cavv_response;
    }

    public function getAccountNumber()
    {
        return $this->account_number;
    }

    public function setAccountNumber($account_number)
    {
        $this->account_number = $account_number;
    }

    public function getCardType()
    {
        return $this->card_type;
    }

    public function setCardType($card_type)
    {
        $this->card_type = $card_type;
    }

    public function getRequestedAmount()
    {
        return $this->requested_amount;
    }

    public function setRequestedAmount($requested_amount)
    {
        $this->requested_amount = $requested_amount;
    }

    public function getBalanceOnCard()
    {
        return $this->balance_on_card;
    }

    public function setBalanceOnCard($balance_on_card)
    {
        $this->balance_on_card = $balance_on_card;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return
            in_array($offset, $this->getSupportedArrayFields()) &&
            property_exists($this, $offset)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->$offset : null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if ($this->offsetExists($offset)) {
            $this->$offset = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            $this->$offset = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $array = array();
        foreach ($this->getSupportedArrayFields() as $name) {
            $array[$name] = $this[$name];
        }

        return new \ArrayIterator(array_filter($array));
    }

    /**
     * @return array
     */
    protected function getSupportedArrayFields()
    {
        $rc = new \ReflectionClass(get_class($this));

        $fields = array();
        foreach ($rc->getProperties() as $rp) {
            $fields[] = $rp->getName();
        }
        
        return $fields;
    }
}