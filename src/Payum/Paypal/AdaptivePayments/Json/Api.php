<?php
namespace Payum\Paypal\AdaptivePayments\Json;

use Http\Message\MessageFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\LogicException;
use Payum\Core\HttpClientInterface;

class Api
{
    const OPERATION_PAY = 'Pay';
    const OPERATION_PAYMENT_DETAILS = 'PaymentDetails';

    const PAY_ACTION_TYPE_PAY = 'PAY';
    const PAY_ACTION_TYPE_CREATE = 'CREATE';
    const PAY_ACTION_TYPE_PAY_PRIMARY = 'PAY_PRIMARY';

    const ACK_SUCCESS = 'Success';
    const ACK_FAILURE = 'Failure';
    const ACK_SUCCESS_WITH_WARNING = 'SuccessWithWarning';
    const ACK_FAILURE_WITH_WARNING = 'FailureWithWarning';

    const DETAIL_LEVEL_RETURN_ALL = 'ReturnAll';

    const ERROR_LANGUAGE_EN_US = 'en_US';

    const PAYMENT_STATUS_CREATED = 'CREATED';
    const PAYMENT_STATUS_COMPLETED = 'COMPLETED';
    const PAYMENT_STATUS_INCOMPLETE = 'INCOMPLETE';
    const PAYMENT_STATUS_ERROR = 'ERROR';
    const PAYMENT_STATUS_REVERSALERROR = 'REVERSALERROR';
    const PAYMENT_STATUS_PROCESSING = 'PROCESSING';
    const PAYMENT_STATUS_PENDING = 'PENDING';

    const FEES_PAYER_SENDER = 'SENDER';
    const FEES_PAYER_PRIMARYRECEIVER = 'PRIMARYRECEIVER';
    const FEES_PAYER_EACHRECEIVER = 'EACHRECEIVER';
    const FEES_PAYER_SECONDARYONLY = 'SECONDARYONLY';

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
        'user_id' => null,
        'password' => null,
        'signature' => null,
        'application_id' => null,
    ];

    /**
     * @param array                    $options
     * @param HttpClientInterface|null $client
     * @param MessageFactory|null      $messageFactory
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $options = ArrayObject::ensureArrayObject($options);
        $options->defaults($this->options);
        $options->validateNotEmpty(array(
            'user_id',
            'password',
            'signature',
            'application_id',
        ));

        if (false == is_bool($options['sandbox'])) {
            throw new InvalidArgumentException('The boolean sandbox option must be set.');
        }

        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @param ArrayObject $fields
     *
     * @return array
     */
    public function pay(ArrayObject $fields)
    {
        return $this->doRequest(self::OPERATION_PAY, $fields);
    }

    /**
     * @param ArrayObject $fields
     *
     * @return array
     */
    public function getPaymentDetails(ArrayObject $fields)
    {
        return $this->doRequest(self::OPERATION_PAYMENT_DETAILS, $fields);
    }

    /**
     * @param $payKey
     *
     * @return string
     */
    public function generateEmbeddedPayKeyAuthorizationUrl($payKey)
    {
        $query = [
            'paykey' => $payKey,
        ];

        return sprintf(
            'https://%s/webapps/adaptivepayment/flow/pay?%s',
            $this->options['sandbox'] ? 'www.sandbox.paypal.com' : 'www.paypal.com',
            http_build_query($query)
        );
    }

    /**
     * @param $payKey
     *
     * @return string
     */
    public function generatePayKeyAuthorizationUrl($payKey)
    {
        return $this->generateAuthorizationUrl(array(
            'cmd' => '_ap-payment',
            'paykey' => $payKey,
        ));
    }

    /**
     * @param $preapprovalKey
     *
     * @return string
     */
    public function generatePreApprovalAuthorizationUrl($preapprovalKey)
    {
        return $this->generateAuthorizationUrl(array(
            'cmd' => '_ap-preapproval',
            'preapprovalkey' => $preapprovalKey,
        ));
    }

    /**
     * @param array $query
     *
     * @return string
     */
    protected function generateAuthorizationUrl(array $query)
    {
        return sprintf(
            'https://%s/cgi-bin/webscr?%s',
            $this->options['sandbox'] ? 'www.sandbox.paypal.com' : 'www.paypal.com',
            http_build_query($query)
        );
    }

    /**
     * @param ArrayObject $fields
     *
     * @throws HttpException
     *
     * @return array
     */
    protected function doRequest($operation, ArrayObject $fields)
    {
        $headers = array(
            'X-PAYPAL-REQUEST-DATA-FORMAT' => 'JSON',
            'X-PAYPAL-RESPONSE-DATA-FORMAT' => 'JSON',
        );

        $this->addVersionHeader($headers);
        $this->addApplicationHeaders($headers);
        $this->addAuthenticationHeaders($headers);
        $this->addDeviceHeaders($fields, $headers);

        $fields = $fields->toUnsafeArrayWithoutLocal();

        $request = $this->messageFactory->createRequest('POST', $this->getApiEndpoint($operation), $headers, json_encode($fields));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param  string
     *
     * @return string
     */
    protected function getApiEndpoint($operation)
    {
        $baseEndpoint = $this->options['sandbox'] ?
            'https://svcs.sandbox.paypal.com/AdaptivePayments/' :
            'https://svcs.paypal.com/AdaptivePayments/'
        ;

        return $baseEndpoint.$operation;
    }

    /**
     * @param array $headers
     */
    protected function addAuthenticationHeaders(array &$headers)
    {
        $headers['X-PAYPAL-SECURITY-USERID'] = $this->options['user_id'];
        $headers['X-PAYPAL-SECURITY-PASSWORD'] = $this->options['password'];
        $headers['X-PAYPAL-SECURITY-SIGNATURE'] = $this->options['signature'];

        if ($this->options['subject']) {
            $headers['X-PAYPAL-SECURITY-SUBJECT'] = $this->options['subject'];
        }
    }

    /**
     * @param array $headers
     */
    protected function addApplicationHeaders(array &$headers)
    {
        $headers['X-PAYPAL-APPLICATION-ID'] = $this->options['application_id'];
    }

    /**
     * @param ArrayObject $fields
     * @param array $headers
     */
    protected function addDeviceHeaders(ArrayObject $fields, array &$headers)
    {
        $local = $fields->getArray('local');

        if ($local['device_id']) {
            $headers['X-PAYPAL-DEVICE-ID'] = $local['device_id'];
        }

        if (false == $local['device_ipaddress']) {
            throw new LogicException('Device ipaddress field is required');
        }

        $headers['X-PAYPAL-DEVICE-IPADDRESS'] = $local['device_ipaddress'];
    }

    /**
     * @param array $headers
     */
    protected function addVersionHeader(array &$headers)
    {
        if ($this->options['version']) {
            $headers['X-PAYPAL-SERVICE-VERSION'] = $this->options['version'];
        }
    }
}
