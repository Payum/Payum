<?php
namespace Payum\Payex\Api;

class OrderApi extends BaseApi
{
    const VIEW_CREDITCARD = 'CREDITCARD';

    const VIEW_MICROACCOUNT = 'MICROACCOUNT';

    const VIEW_DIRECTDEBIT = 'DIRECTDEBIT';

    /**
     * Norwegian and Swedish overcharged SMS
     */
    const VIEW_CPA = 'CPA';

    /**
     * Overcharged call
     */
    const VIEW_IVR = 'IVR';

    /**
     * Value code
     */
    const VIEW_EVC = 'EVC';

    const VIEW_INVOICE = 'INVOICE';

    const VIEW_LOAN = 'LOAN';

    /**
     * Gift card / generic card
     */
    const VIEW_GC = 'GC';

    /**
     * Credit account
     */
    const VIEW_CA = 'GC';

    /**
     * PayPal transactions
     */
    const VIEW_PAYPAL = 'PAYPAL';

    const VIEW_FINANCING = 'FINANCING';

    const TRANSACTIONSTATUS_SALE = 0;

    const TRANSACTIONSTATUS_INITIALIZE = 1;

    const TRANSACTIONSTATUS_CREDIT = 2;

    const TRANSACTIONSTATUS_AUTHORIZE = 3;

    const TRANSACTIONSTATUS_CANCEL = 4;

    const TRANSACTIONSTATUS_FAILURE = 5;

    const TRANSACTIONSTATUS_CAPTURE = 6;

    /**
     * Returns the Status of the order0 = The order is completed (a purchase has been done, but check the transactionStatus to see the result).
     */
    const ORDERSTATUS_COMPLETED = 0;

    /**
     * 1 = The order is processing. The customer has not started the purchase. PxOrder.Complete can return orderStatus 1 for 2 weeks after PxOrder.Initialize is called. Afterwards the orderStatus will be set to 2
     */
    const ORDERSTATUS_PROCESSING = 1;

    /**
     * 2 = No order or transaction is found
     */
    const ORDERSTATUS_NOT_FOUND = 2;

    /**
     * @link http://www.payexpim.com/technical-reference/pxorder/initialize8/
     *
     * @var array $parameters
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
     * @param array $parameters
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
     * @param array $parameters
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

    /**
     * {@inheritDoc}
     */
    protected function getPxOrderWsdl()
    {
        return $this->options['sandbox'] ?
            'https://test-external.payex.com/pxorder/pxorder.asmx?wsdl' :
            'https://external.payex.com/pxorder/pxorder.asmx?wsdl'
        ;
    }
}
