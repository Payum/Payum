<?php

namespace Payum\Sofort;

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

    protected $options = [
        'config_key' => null,
    ];

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
        $fields = (array_replace([
            'success_url' => null,
            'success_link_redirect' => true,
            'abort_url' => null,
            'notification_url' => null,
            'notify_on' => implode(',', [self::STATUS_PENDING, self::STATUS_LOSS, self::STATUS_RECEIVED, self::STATUS_REFUNDED, self::STATUS_UNTRACEABLE]),
            'reason' => '',
            'reason_2' => '',
            'product_code' => null,
        ], $fields));

        $sofort = new Sofortueberweisung($this->options['config_key']);
        $sofort->setAmount($fields['amount']);
        $sofort->setCurrencyCode($fields['currency_code']);
        $sofort->setReason($fields['reason'], $fields['reason_2'], $fields['product_code']);

        $sofort->setSuccessUrl($fields['success_url'], $fields['success_link_redirect']);
        $sofort->setAbortUrl($fields['abort_url']);
        $sofort->setNotificationUrl($fields['notification_url'], $fields['notify_on']);

        $sofort->sendRequest();

        return array_filter([
            'error' => $sofort->getError(),
            'transaction_id' => $sofort->getTransactionId(),
            'payment_url' => $sofort->getPaymentUrl(),
        ]);
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

        if ($refund->isError()) {
            $fields['refund_error'] = $refund->getError();
        } else {
            $fields['refund_url'] = $refund->getPaymentUrl();
        }

        return $fields;
    }
}
