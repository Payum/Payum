<?php

namespace Payum\Payex\Api;

class OrderApi extends BaseApi
{
    public const VIEW_CREDITCARD = 'CREDITCARD';

    public const VIEW_MICROACCOUNT = 'MICROACCOUNT';

    public const VIEW_DIRECTDEBIT = 'DIRECTDEBIT';

    /**
     * Norwegian and Swedish overcharged SMS
     */
    public const VIEW_CPA = 'CPA';

    /**
     * Overcharged call
     */
    public const VIEW_IVR = 'IVR';

    /**
     * Value code
     */
    public const VIEW_EVC = 'EVC';

    public const VIEW_INVOICE = 'INVOICE';

    public const VIEW_LOAN = 'LOAN';

    /**
     * Gift card / generic card
     */
    public const VIEW_GC = 'GC';

    /**
     * Credit account
     */
    public const VIEW_CA = 'GC';

    /**
     * PayPal transactions
     */
    public const VIEW_PAYPAL = 'PAYPAL';

    public const VIEW_FINANCING = 'FINANCING';

    public const TRANSACTIONSTATUS_SALE = 0;

    public const TRANSACTIONSTATUS_INITIALIZE = 1;

    public const TRANSACTIONSTATUS_CREDIT = 2;

    public const TRANSACTIONSTATUS_AUTHORIZE = 3;

    public const TRANSACTIONSTATUS_CANCEL = 4;

    public const TRANSACTIONSTATUS_FAILURE = 5;

    public const TRANSACTIONSTATUS_CAPTURE = 6;

    /**
     * Returns the Status of the order0 = The order is completed (a purchase has been done, but check the transactionStatus to see the result).
     */
    public const ORDERSTATUS_COMPLETED = 0;

    /**
     * 1 = The order is processing. The customer has not started the purchase. PxOrder.Complete can return orderStatus 1 for 2 weeks after PxOrder.Initialize is called. Afterwards the orderStatus will be set to 2
     */
    public const ORDERSTATUS_PROCESSING = 1;

    /**
     * 2 = No order or transaction is found
     */
    public const ORDERSTATUS_NOT_FOUND = 2;

    /**
     * @link http://www.payexpim.com/technical-reference/pxorder/initialize8/
     *
     * @var array
     *
     * @return array
     */
    public function initialize(array $parameters)
    {
        $parameters['accountNumber'] = $this->options['account_number'];

        //DEPRICATED. Send in as empty string.
        $parameters['externalID'] = '';

        if (isset($parameters['orderId'])) {
            //On request it requires orderID fields when in response it is orderId.
            $parameters['orderID'] = $parameters['orderId'];
            unset($parameters['orderId']);
        }

        $parameters['hash'] = $this->calculateHash($parameters, array(
            'accountNumber',
            'purchaseOperation',
            'price',
            'priceArgList',
            'currency',
            'vat',
            'orderID',
            'productNumber',
            'description',
            'clientIPAddress',
            'clientIdentifier',
            'additionalValues',
            'externalID',
            'returnUrl',
            'view',
            'agreementRef',
            'cancelUrl',
            'clientLanguage',
        ));

        return $this->call('Initialize8', $parameters, $this->getPxOrderWsdl());
    }

    /**
     * @link http://www.payexpim.com/technical-reference/pxorder/complete-2/
     *
     * @return array
     */
    public function complete(array $parameters)
    {
        $parameters['accountNumber'] = $this->options['account_number'];

        $parameters['hash'] = $this->calculateHash($parameters, array(
            'accountNumber',
            'orderRef',
        ));

        return $this->call('Complete', $parameters, $this->getPxOrderWsdl());
    }

    /**
     * @link http://www.payexpim.com/technical-reference/pxorder/check2/
     *
     * @return array
     */
    public function check(array $parameters)
    {
        $parameters['accountNumber'] = $this->options['account_number'];

        $parameters['hash'] = $this->calculateHash($parameters, array(
            'accountNumber',
            'transactionNumber',
        ));

        return $this->call('Check2', $parameters, $this->getPxOrderWsdl());
    }

    protected function getPxOrderWsdl()
    {
        return $this->options['sandbox'] ?
            'https://test-external.payex.com/pxorder/pxorder.asmx?wsdl' :
            'https://external.payex.com/pxorder/pxorder.asmx?wsdl'
        ;
    }
}
