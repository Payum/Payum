<?php
namespace Payum\Paypal\Ipn;

use Http\Message\MessageFactory;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\HttpClientInterface;

/**
 * @link https://www.x.com/developers/paypal/documentation-tools/ipn/integration-guide/IPNIntro
 */
class Api
{
    /**
     * It sends back if the message originated with PayPal.
     */
    const NOTIFY_VERIFIED = 'VERIFIED';

    /**
     * if there is any discrepancy with what was originally sent
     */
    const NOTIFY_INVALID = 'INVALID';

    const CMD_NOTIFY_VALIDATE = '_notify-validate';

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
    protected $options;

    /**
     * @param array               $options
     * @param HttpClientInterface $client
     * @param MessageFactory      $messageFactory
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $this->client = $client;
        $this->messageFactory = $messageFactory;

        $this->options = $options;

        if (false == (isset($this->options['sandbox']) && is_bool($this->options['sandbox']))) {
            throw new InvalidArgumentException('The boolean sandbox option must be set.');
        }
    }

    /**
     * @param array $fields
     *
     * @return string
     */
    public function notifyValidate(array $fields)
    {
        $fields['cmd'] = self::CMD_NOTIFY_VALIDATE;

        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
        );

        $request = $this->messageFactory->createRequest('POST', $this->getIpnEndpoint(), $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        $result = $response->getBody()->getContents();

        return self::NOTIFY_VERIFIED === $result ? self::NOTIFY_VERIFIED : self::NOTIFY_INVALID;
    }

    /**
     * @return string
     */
    public function getIpnEndpoint()
    {
        return $this->options['sandbox'] ?
            'https://www.sandbox.paypal.com/cgi-bin/webscr' :
            'https://www.paypal.com/cgi-bin/webscr'
        ;
    }
}
