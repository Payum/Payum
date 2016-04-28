<?php
namespace Payum\Paypal\ProHosted;

use Http\Message\MessageFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\HttpClientInterface;
use Payum\Core\Reply\HttpPostRedirect;

class Api
{
    const VERSION = '65.2';
    const ACK_SUCCESS = 'Success';
    const ACK_SUCCESS_WITH_WARNING = 'SuccessWithWarning';
    const ACK_FAILURE = 'Failure';
    const ACK_FAILUREWITHWARNING = 'FailureWithWarning';
    const ACK_WARNING = 'Warning';
    const CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED = 'PaymentActionNotInitiated';
    const CHECKOUTSTATUS_PAYMENT_ACTION_FAILED = 'PaymentActionFailed';
    const CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS = 'PaymentActionInProgress';
    const CHECKOUTSTATUS_PAYMENT_COMPLETED = 'PaymentCompleted';
    const CHECKOUTSTATUS_PAYMENT_ACTION_COMPLETED = 'PaymentActionCompleted';

    /**
     * No status
     */
    const PAYMENTSTATUS_NONE = 'None';

    /**
     * A reversal has been canceled; for example, when you win a dispute and the funds for the reversal have been returned to you.
     */
    const PAYMENTSTATUS_CANCELED_REVERSAL = 'Canceled-Reversal';

    /**
     * The payment has been completed, and the funds have been added successfully to your account balance.
     */
    const PAYMENTSTATUS_COMPLETED = 'Completed';

    /**
     * You denied the payment. This happens only if the payment was previously pending because of possible reasons described for the PendingReason element.
     */
    const PAYMENTSTATUS_DENIED = 'Denied';

    /**
     * The authorization period for this payment has been reached.
     */
    const PAYMENTSTATUS_EXPIRED = 'Expired';

    /**
     * The payment has failed. This happens only if the payment was made from your buyer's bank account.
     */
    const PAYMENTSTATUS_FAILED = 'Failed';

    /**
     * The transaction has not terminated, e.g. an authorization may be awaiting completion.
     */
    const PAYMENTSTATUS_IN_PROGRESS = 'In-Progress';

    /**
     * The payment has been partially refunded.
     */
    const PAYMENTSTATUS_PARTIALLY_REFUNDED = 'Partially-Refunded';

    /**
     * The payment is pending. See the PendingReason field for more information.
     */
    const PAYMENTSTATUS_PENDING = 'Pending';

    /**
     * A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer. The reason for the reversal is specified in the ReasonCode element.
     */
    const PAYMENTSTATUS_REVERSED = 'Reversed';

    /**
     * You refunded the payment.
     */
    const PAYMENTSTATUS_REFUNDED = 'Refunded';

    /**
     *  A payment has been accepted.
     */
    const PAYMENTSTATUS_PROCESSED = 'Processed';
    const PAYERSTATUS_VERIFIED = 'verified';
    const PAYERSTATUS_UNVERIFIED = 'unverified';

    const PENDINGREASON_AUTHORIZATION = 'authorization';

    /**
     * Payment has not been authorized by the user.
     */
    const L_ERRORCODE_PAYMENT_NOT_AUTHORIZED = 10485;

    /**
     * This Express Checkout session has expired.
     */
    const L_ERRORCODE_SESSION_HAS_EXPIRED = 10411;

    const PAYMENTACTION_SALE = 'sale';
    const FORM_CMD = '_hosted-payment';

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
    protected $options = array(
        'username'   => null,
        'password'   => null,
        'signature'  => null,
        'business'   => null,
        'bn'         => null,
        'return_url' => null,
        'cancel_url' => null,
        'sandbox'    => null,
        'cmd'        => Api::FORM_CMD,
    );

    /**
     * @param array               $options
     * @param HttpClientInterface $client
     * @param MessageFactory      $messageFactory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $options = ArrayObject::ensureArrayObject($options);
        $options->defaults($this->options);
        $options->validateNotEmpty([
                                       'username',
                                       'password',
                                       'signature',
                                       'business',
                                   ]

        );

        if (false == is_bool($options['sandbox'])) {
            throw new InvalidArgumentException('The boolean sandbox option must be set.');
        }

        $this->options        = $options;
        $this->client         = $client;
        $this->messageFactory = $messageFactory;
    }

    /**
     * Solution HTML
     *
     * @param array $fields
     *
     * @return array
     */
    public function doSale(array $fields)
    {
        $fields['paymentaction'] = self::PAYMENTACTION_SALE;
        $fields['cmd']           = self::FORM_CMD;

        $this->addVersionField($fields);
        $this->addAuthorizeFields($fields);

        throw new HttpPostRedirect(
            $this->getApiEndpoint(),
            $fields
        );
    }

    /**
     * Solution BMCreateButton
     *
     * @param array $fields
     *
     * @return array
     */
    public function doCreateButton(array $fields)
    {
        $this->addAuthorizeFields($fields);

        $fields['paymentaction'] = self::PAYMENTACTION_SALE;
        $fields['cmd']           = self::FORM_CMD;

        $newFields = [];
        $i = 0;
        foreach ($fields as $key => $val) {
            $newFields['L_BUTTONVAR'.$i.'='.$key] = $val;
            $i++;
        }
        $newFields['L_BUTTONVAR'.$i] = 'subtotal='.$fields['subtotal'];
        $newFields['METHOD']         = 'BMCreateButton';
        $newFields['BUTTONTYPE']     = 'PAYMENT';
        $newFields['BUTTONCODE']     = 'TOKEN';

        $this->addVersionField($newFields);
        $this->addAuthorizeNvpFields($newFields);

        $response = $this->doRequest($newFields, $this->getNvpEndpoint());

        return $response;
    }

    /**
     * @param array  $fields
     * @param string $endPoint
     *
     * @throws HttpException
     *
     * @return array
     */
    protected function doRequest(array $fields, $endPoint)
    {
        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
        );

        $request = $this->messageFactory->createRequest('POST', $endPoint, $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        $result = array();
        parse_str($response->getBody()->getContents(), $result);

        foreach ($result as &$value) {
            $value = urldecode($value);
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getApiEndpoint()
    {
        return $this->options['sandbox'] ?
            'https://www.sandbox.paypal.com/acquiringwebr' :
            'https://securepayments.paypal.com/acquiringweb';
    }

    /**
     * @return string
     */
    protected function getNvpEndpoint()
    {
        return $this->options['sandbox'] ?
            'https://api-3t.sandbox.paypal.com/nvp' :
            'https://api-3t.paypal.com/nvp';
    }

    /**
     * @param array $fields
     */
    protected function addAuthorizeFields(array &$fields)
    {
        $fields['business'] = $this->options['business'];
    }

    /**
     * @param array $fields
     */
    protected function addVersionField(array &$fields)
    {
        $fields['VERSION'] = self::VERSION;
    }

    /**
     * Require: txn_id
     *
     * @param array $fields
     *
     * @return array
     */
    public function getTransactionDetails($fields)
    {
        $fields['METHOD'] = 'GetTransactionDetails';

        $this->addAuthorizeNvpFields($fields);
        $this->addAuthorizeFields($fields);
        $this->addVersionField($fields);

        var_dump($fields);

        return $this->doRequest($fields, $this->getNvpEndpoint());
    }

    /**
     * @param array $fields
     */
    protected function addAuthorizeNvpFields(array &$fields)
    {
        $fields['USER']      = $this->options['username'];
        $fields['PWD']       = $this->options['password'];
        $fields['SIGNATURE'] = $this->options['signature'];
    }

    /**
     * @return bool
     */
    public function isEnvironnementTest()
    {
        return $this->options['sandbox'];
    }
}

