<?php

namespace Invit\PayumSofort;

use Sofort\SofortLib\Refund;
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

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    public function createTransaction(array $fields)
    {
        $sofort = new Sofortueberweisung($this->options['config_key']);
        $sofort->setAmount($fields['amount']);
        $sofort->setCurrencyCode($fields['currency_code']);
        $sofort->setReason($fields['reason']);
        $sofort->setSuccessUrl($fields['success_url'], true);
        $sofort->setAbortUrl($fields['success_url']);
        $sofort->setNotificationUrl($fields['notification_url'], 'received');
        $sofort->sendRequest();

        if ($sofort->isError()) {
            $fields['error'] = $sofort->getError();
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

        foreach ($methods as $method => $params) {
            $varName = $method;
            $varName = strtolower(preg_replace('/([^A-Z])([A-Z])/', '$1_$2', substr($varName, 3)));

            if (count($params) == 2) {
                $fields[$varName] = $transactionData->$method($params[0], $params[1]);
            } elseif ($params !== '') {
                $fields[$varName] = $transactionData->$method($params);
            } else {
                $fields[$varName] = $transactionData->$method();
            }
        }

        if ($transactionData->isError()) {
            $fields['error'] = $transactionData->getError();
        }

        return $fields;
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    public function refundTransaction(array $fields)
    {
        $refund = new Refund($this->options['config_key']);
        $refund->setSenderSepaAccount($fields['recipient_bic'], $fields['recipient_iban'], $fields['recipient_holder']);
        $refund->addRefund($fields['transaction_id'], $fields['refund_amount']);
        $refund->setPartialRefundId(md5(uniqid()));
        $refund->setReason($fields['reason']);
        $refund->sendRequest();

        if($refund->isError()) {
            $fields['refund_error'] = $refund->getError();
        } else {
            $fields['refund_url'] = $refund->getPaymentUrl();
        }
        return $fields;
    }
}
