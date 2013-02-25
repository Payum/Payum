<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Examples\Model;

use Payum\Paypal\ExpressCheckout\Nvp\Api;

class AwesomeCart 
{
    public function getId()
    {
        return 1;
    }
    
    public function getPrice()
    {
        return 10;
    }
    
    public function getCurrency()
    {
        return 'USD';
    }
    
    public function setPaymentDetails()
    {
        
    }
    
    public function getPaymentDetails()
    {
        return array(
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTREQUEST_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED
        );
    }
}