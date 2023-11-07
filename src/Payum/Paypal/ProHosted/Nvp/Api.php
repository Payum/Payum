<?php

namespace Payum\Paypal\ProHosted\Nvp;

use Http\Message\MessageFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\HttpClientInterface;

/**
 * @link https://developer.paypal.com/webapps/developer/docs/classic/products/website-payments-pro-hosted-solution
 * @link https://www.paypalobjects.com/webstatic/en_GB/developer/docs/pdf/hostedsolution_uk.pdf
 * @link https://developer.paypal.com/docs/classic/api/button-manager/BMCreateButton_API_Operation_NVP/
 * L_ERRORCODE @link https://developer.paypal.com/webapps/developer/docs/classic/api/errorcodes/#id09C3GA00GR1
 */
class Api
{
    public const VERSION = '65.2';

    public const ACK_SUCCESS = 'Success';

    public const ACK_SUCCESS_WITH_WARNING = 'SuccessWithWarning';

    public const ACK_FAILURE = 'Failure';

    public const ACK_FAILUREWITHWARNING = 'FailureWithWarning';

    public const ACK_WARNING = 'Warning';

    /**
     * No status
     */
    public const PAYMENTSTATUS_NONE = 'None';

    /**
     * A reversal has been canceled; for example, when you win a dispute and the funds for the reversal have been returned to you.
     */
    public const PAYMENTSTATUS_CANCELED_REVERSAL = 'Canceled-Reversal';

    /**
     * The payment has been completed, and the funds have been added successfully to your account balance.
     */
    public const PAYMENTSTATUS_COMPLETED = 'Completed';

    /**
     * You denied the payment. This happens only if the payment was previously pending because of possible reasons described for the PendingReason element.
     */
    public const PAYMENTSTATUS_DENIED = 'Denied';

    /**
     * The authorization period for this payment has been reached.
     */
    public const PAYMENTSTATUS_EXPIRED = 'Expired';

    /**
     * The payment has failed. This happens only if the payment was made from your buyer's bank account.
     */
    public const PAYMENTSTATUS_FAILED = 'Failed';

    /**
     * The transaction has not terminated, e.g. an authorization may be awaiting completion.
     */
    public const PAYMENTSTATUS_IN_PROGRESS = 'In-Progress';

    /**
     * The payment has been partially refunded.
     */
    public const PAYMENTSTATUS_PARTIALLY_REFUNDED = 'Partially-Refunded';

    /**
     * The payment is pending. See the PendingReason field for more information.
     */
    public const PAYMENTSTATUS_PENDING = 'Pending';

    /**
     * A payment was reversed due to a chargeback or other type of reversal.
     * The funds have been removed from your account balance and returned to the buyer.
     * The reason for the reversal is specified in the ReasonCode element.
     */
    public const PAYMENTSTATUS_REVERSED = 'Reversed';

    /**
     * You refunded the payment.
     */
    public const PAYMENTSTATUS_REFUNDED = 'Refunded';

    /**
     *  A payment has been accepted.
     */
    public const PAYMENTSTATUS_PROCESSED = 'Processed';

    public const PAYERSTATUS_VERIFIED = 'verified';

    public const PAYERSTATUS_UNVERIFIED = 'unverified';

    public const PENDINGREASON_AUTHORIZATION = 'authorization';

    public const PAYMENTACTION_SALE = 'sale';

    public const FORM_CMD = '_hosted-payment';

    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var array
     */
    protected $options = [
        'username' => null,
        'password' => null,
        'signature' => null,
        'business' => null,
        'return' => null,
        'sandbox' => null,
        'cmd' => self::FORM_CMD,
    ];

    /**
     * @throws InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $options = ArrayObject::ensureArrayObject($options);
        $options->defaults($this->options);
        $options->validateNotEmpty([
            'username',
            'password',
            'signature',
        ]);

        if (false == is_bool($options['sandbox'])) {
            throw new InvalidArgumentException('The boolean sandbox option must be set.');
        }

        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    /**
     * Solution BMCreateButton
     *
     * @throws RuntimeException
     *
     * @return array
     */
    public function doCreateButton(array $fields)
    {
        if (false == isset($fields['return'])) {
            if (false == $this->options['return']) {
                throw new RuntimeException('The return must be set either to FormRequest or to options.');
            }

            $fields['return'] = $this->options['return'];
        }

        $fields['paymentaction'] = self::PAYMENTACTION_SALE;
        $fields['cmd'] = self::FORM_CMD;

        $newFields = [];
        $i = 0;
        foreach ($fields as $key => $val) {
            $newFields['L_BUTTONVAR' . $i] = $key . '=' . $val;
            $i++;
        }

        $newFields['METHOD'] = 'BMCreateButton';
        $newFields['BUTTONTYPE'] = 'PAYMENT';
        $newFields['BUTTONCODE'] = 'TOKEN';

        $this->addVersionField($newFields);
        $this->addAuthorizeFields($newFields);

        return $this->doRequest($newFields);
    }

    /**
     * Require: TRANSACTIONID
     *
     * @param array $fields
     *
     * @return array
     */
    public function getTransactionDetails($fields)
    {
        $fields['METHOD'] = 'GetTransactionDetails';

        $this->addAuthorizeFields($fields);
        $this->addVersionField($fields);

        return $this->doRequest($fields);
    }

    /**
     * @return bool
     */
    public function isEnvironnementTest()
    {
        return $this->options['sandbox'];
    }

    /**
     * @throws HttpException
     *
     * @return array
     */
    protected function doRequest(array $fields)
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $request = $this->messageFactory->createRequest('POST', $this->getApiEndpoint(), $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        parse_str($response->getBody()->getContents(), $result);

        foreach ($result as &$value) {
            $value = urldecode($value);
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        return $this->options['sandbox'] ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
    }

    protected function addAuthorizeFields(array &$fields)
    {
        $fields['USER'] = $this->options['username'];
        $fields['PWD'] = $this->options['password'];
        $fields['SIGNATURE'] = $this->options['signature'];

        if ($this->options['business']) {
            $fields['BUSINESS'] = $this->options['business'];
            $fields['SUBJECT'] = $this->options['business'];
        }
    }

    protected function addVersionField(array &$fields)
    {
        $fields['VERSION'] = self::VERSION;
    }
}
