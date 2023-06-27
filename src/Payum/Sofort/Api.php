<?php

namespace Payum\Sofort;

use Sofort\SofortLib\Refund;
use Sofort\SofortLib\Sofortueberweisung;
use Sofort\SofortLib\TransactionData;

class Api
{
    public const STATUS_LOSS = 'loss';

    public const SUB_LOSS = 'not_credited';

    public const STATUS_PENDING = 'pending';

    public const SUB_PENDING = 'not_credited_yet';

    public const STATUS_RECEIVED = 'received';

    public const SUB_CREDITED = 'credited';

    public const SUB_PARTIALLY = 'partially_credited';

    public const SUB_OVERPAYMENT = 'overpayment';

    public const STATUS_REFUNDED = 'refunded';

    public const SUB_COMPENSATION = 'compensation';

    public const SUB_REFUNDED = 'refunded';

    public const STATUS_UNTRACEABLE = 'untraceable';

    public const SUB_SOFORT_NEEDED = 'sofort_bank_account_needed';

    /**
     * @var array{config_key: ?string, abort_url?: ?string, disable_notification?: bool}
     */
    protected array $options = [
        'config_key' => null,
    ];

    public function __construct(array $options)
    {
        $this->options = array_replace([
            'config_key' => null,
            'abort_url' => null,
            'disable_notification' => false,
        ], $options);
    }

    /**
     * @return array<string, mixed>
     */
    public function createTransaction(array $fields): array
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

        if (false == $this->options['disable_notification']) {
            $sofort->setNotificationUrl($fields['notification_url'], $fields['notify_on']);
        }

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
     * @return array<string, mixed>
     */
    public function getTransactionData($transactionId): array
    {
        $transactionData = new TransactionData($this->options['config_key']);
        $transactionData->addTransaction($transactionId);
        $transactionData->setApiVersion('2.0');
        $transactionData->sendRequest();

        $fields = [];
        $methods = [
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
            'getReason' => [0, 0],
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
        ];

        foreach ($methods as $method => $params) {
            $varName = $method;
            $varName = strtolower(preg_replace('/([^A-Z])([A-Z])/', '$1_$2', substr($varName, 3)));

            if (is_array($params) && 2 == count($params)) {
                $fields[$varName] = $transactionData->{$method}($params[0], $params[1]);
            } elseif ('' !== $params) {
                $fields[$varName] = $transactionData->{$method}($params);
            } else {
                $fields[$varName] = $transactionData->{$method}();
            }
        }

        if ($transactionData->isError()) {
            $fields['error'] = $transactionData->getError();
        }

        return $fields;
    }

    /**
     * @return mixed[]
     */
    public function refundTransaction(array $fields): array
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
