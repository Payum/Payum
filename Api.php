<?php

namespace Invit\PayumSofort;


use Sofort\SofortLib\Sofortueberweisung;
use Sofort\SofortLib\TransactionData;

class Api
{
    const STATUS_LOSS = 'loss';
    const SUB_LOSS = 'not_credited';
    const STATUS_PENDING = 'pending';
    const SUB_PENDING = 'not_credited_yet';
    const STATUS_RECEIVED = 'received';
    const SUB_CREDITED = 'credited';
    const SUB_PARTIALLY = 'partially_credited';
    const SUB_OVERPAYMENT = 'overpayment';
    const STATUS_REFUNDED = 'refunded';
    const SUB_COMPENSATION = 'compensation';
    const SUB_REFUNDED = 'refunded';
    const STATUS_UNTRACEABLE = 'untraceable';
    const SUB_SOFORT_NEEDED = 'sofort_bank_account_needed';

    protected $options = array(
        'config_key' => null,
        'sandbox' => null,
    );

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    private function initRequest()
    {
        $sofort = new Sofortueberweisung($this->options['config_key']);

        return $sofort;
    }

    public function createTransaction(array $fields)
    {
        $sofort = $this->initRequest();

        $sofort->setAmount($fields['amount']);
        $sofort->setCurrencyCode($fields['currency_code']);
        $sofort->setReason('Testueberweisung', 'Verwendungszweck');
        $sofort->setSuccessUrl($fields['success_url'], true);
        $sofort->setAbortUrl($fields['success_url']);
        $sofort->setNotificationUrl($fields['notification_url'], 'received');
        $sofort->sendRequest();

        dump($sofort->getData());
        dump($sofort->getResponse());

        if($sofort->isError()) {
            //SOFORT-API didn't accept the data
            echo $sofort->getError();
        } else {
            $fields['transaction_id'] = $sofort->getTransactionId();
            $fields['payment_url'] = $sofort->getPaymentUrl();
        }

        return $fields;
    }

    /**
     * @param $transactionId
     *
     * @return array
     */
    public function getTransactionData($transactionId)
    {
        $transactionData = new TransactionData($this->options['config_key']);
        $transactionData->addTransaction($transactionId);
        $transactionData->setApiVersion('2.0');
        $transactionData->sendRequest();

        $fields = array();
        $methods = array(
            'getAmount' => '',
            'getAmountRefunded' => '',
            'getCount' => '',
            'getPaymentMethod' => '',
            'getConsumerProtection' => '',
            'getStatus' => '',
            'getStatusReason' => '',
            'getStatusModifiedTime' => '',
            'getLanguageCode' => '',
            'getCurrency' => '',
            'getTransaction' => '',
            'getReason' => array(0,0),
            'getUserVariable' => 0,
            'getTime' => '',
            'getProjectId' => '',
            'getRecipientHolder' => '',
            'getRecipientAccountNumber' => '',
            'getRecipientBankCode' => '',
            'getRecipientCountryCode' => '',
            'getRecipientBankName' => '',
            'getRecipientBic' => '',
            'getRecipientIban' => '',
            'getSenderHolder' => '',
            'getSenderAccountNumber' => '',
            'getSenderBankCode' => '',
            'getSenderCountryCode' => '',
            'getSenderBankName' => '',
            'getSenderBic' => '',
            'getSenderIban' => '',
        );

        foreach($methods as $method => $params) {
            $varName = $method;
            $varName = strtolower(preg_replace('/([^A-Z])([A-Z])/', "$1_$2", substr($varName, 3)));

            if(count($params) == 2) {
                $fields[$varName] = $transactionData->$method($params[0], $params[1]);
            } else if($params !== '') {
                $fields[$varName] = $transactionData->$method($params);
            } else {
                $fields[$varName] = $transactionData->$method();
            }
        }

        if($transactionData->isError()) {
            $fields['error'] = $transactionData->getError();
        }

        return $fields;
    }
}