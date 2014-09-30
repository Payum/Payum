<?php
namespace Payum\Paypal\AdaptivePayments;

use Buzz\Client\ClientInterface;
use Buzz\Client\Curl;
use Buzz\Message\Form\FormRequest;
use Buzz\Message\Response;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\RuntimeException;

class Api
{
    /**
     * All Adaptive API calls in sandbox mode should have this
     */
    const SANDBOX_APP_ID = 'APP-80W284485P519543T';

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var string[]
     */
    protected $options = array(
        'username' => null,
        'password' => null,
        'signature' => null,
        'subject' => null,
        'app_id' => null,
        'sandbox' => null
    );

    /**
     * @param array $options
     * @param ClientInterface|null $client
     */
    public function __construct(array $options, ClientInterface $client = null)
    {
        $this->client = $client ?: new Curl;

        $this->options = array_replace($this->options, $options);

        if (true == empty($this->options['username'])) {
            throw new InvalidArgumentException('The username option must be set.');
        }

        if (true == empty($this->options['password'])) {
            throw new InvalidArgumentException('The password option must be set.');
        }

        if (true == empty($this->options['subject'])) {
            throw new InvalidArgumentException('The subject option must be set.');
        }

        if (true == empty($this->options['signature'])) {
            throw new InvalidArgumentException('The signature option must be set.');
        }

        if (true == empty($this->options['app_ip']) && !$this->options['sandbox']) {
            throw new InvalidArgumentException('The app_id option must be set (except when sandbox=true).');
        } else {
            $this->options['app_id'] = Api::SANDBOX_APP_ID;
        }
        if (false == is_bool($this->options['sandbox'])) {
            throw new InvalidArgumentException('The boolean sandbox option must be set.');
        }
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    public function pay(array $fields)
    {
        $request = new FormRequest;
        $request->setFields($fields);

        if (false == isset($fields['returnUrl'])) {
            if (false == $this->options['return_url']) {
                throw new RuntimeException('The return_url must be set either to FormRequest or to options.');
            }

            $request->setField('returnUrl', $this->options['return_url']);
        }

        if (false == isset($fields['cancelUrl'])) {
            if (false == $this->options['cancel_url']) {
                throw new RuntimeException('The cancel_url must be set either to FormRequest or to options.');
            }

            $request->setField('cancelUrl', $this->options['cancel_url']);
        }

        $request->setField('actionType', 'Pay');
        $request->fromUrl($this->getApiUrl('Pay'));

        $this->addHeaders($request);

        return $this->doRequest($request);
    }

    /**
     * @param string $payKey
     *
     * @return array
     */
    public function paymentDetails($payKey)
    {
        $request = new FormRequest;
        $request->setField('payKey', $payKey);

        $request->fromUrl($this->getApiUrl('PaymentDetails'));

        $this->addHeaders($request);

        return $this->doRequest($request);
    }

    /**
     * @param string $payKey
     *
     * @return string
     */
    public function getAuthorizePayKeyUrl($payKey)
    {
        $query = array_filter(array(
            'cmd' => '_ap-payment',
            'paykey' => $payKey,
        ));

        return sprintf(
            'https://%s/cgi-bin/webscr?%s',
            $this->options['sandbox'] ? 'www.sandbox.paypal.com' : 'www.paypal.com',
            http_build_query($query)
        );
    }

    /**
     * @param FormRequest $request
     *
     * @throws HttpException
     *
     * @return array
     */
    protected function doRequest(FormRequest $request)
    {
        $request->setMethod('POST');

        $this->client->send($request, $response = new Response);

        if (false == $response->isSuccessful()) {
            throw HttpException::factory($request, $response);
        }

        $result = array();
        parse_str($response->getContent(), $result);
        foreach ($result as &$value) {
            $value = urldecode($value);
        }

        return $result;
    }

    /**
     * @param string $method
     *
     * @return string
     */
    protected function getApiUrl($method)
    {
        return $this->options['sandbox'] ?
            "https://svcs.sandbox.paypal.com/AdaptivePayments/$method" :
            "https://svcs.paypal.com/AdaptivePayments/$method"
        ;
    }

    /**
     * @param FormRequest $request
     */
    protected function addHeaders(FormRequest $request)
    {
        $request->addHeader('X-PAYPAL-SECURITY-PASSWORD: ' . $this->options['password']);
        $request->addHeader('X-PAYPAL-SECURITY-USERID: ' . $this->options['username']);
        $request->addHeader('X-PAYPAL-SECURITY-SIGNATURE: ' . $this->options['signature']);
        $request->addHeader('X-PAYPAL-SECURITY-SUBJECT: ' . $this->options['subject']);
        $request->addHeader('X-PAYPAL-APPLICATION-ID: ' . $this->options['app_id']);
        $request->addHeader('X-PAYPAL-REQUEST-DATA-FORMAT: NV');
        $request->addHeader('X-PAYPAL-RESPONSE-DATA-FORMAT: NV');
    }
}