<?php
namespace Payum\Paypal\ExpressCheckout\Nvp;

class APApi
{

    const CMD_ADAPTIVE_PAYMENT = '_ap-payment';
    
    const CMD_ADAPTIVE_PREAPPROVAL = '_ap-preapproval';

	//TODO

    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        return $this->options['sandbox'] ?
            'https://svcs.sandbox.paypal.com/AdaptivePayments/API_operation' :
            'https://svcs.paypal.com/AdaptivePayments/API_operation'
        ;
    }

}