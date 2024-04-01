<?php

namespace Payum\Paypal\Ipn;

use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\InvalidArgumentException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * @link https://www.x.com/developers/paypal/documentation-tools/ipn/integration-guide/IPNIntro
 */
class Api
{
    /**
     * It sends back if the message originated with PayPal.
     */
    public const NOTIFY_VERIFIED = 'VERIFIED';

    /**
     * if there is any discrepancy with what was originally sent
     */
    public const NOTIFY_INVALID = 'INVALID';

    public const CMD_NOTIFY_VALIDATE = '_notify-validate';

    protected ClientInterface $client;

    protected RequestFactoryInterface $requestFactory;

    protected StreamFactoryInterface $streamFactory;

    /**
     * @var array<string, mixed>
     */
    protected array $options;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        array $options,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;

        $this->options = $options;

        if (! (isset($this->options['sandbox']) && is_bool($this->options['sandbox']))) {
            throw new InvalidArgumentException('The boolean sandbox option must be set.');
        }
        $this->streamFactory = $streamFactory;
    }

    /**
     * @param array<string, mixed> $fields
     * @throws ClientExceptionInterface
     */
    public function notifyValidate(array $fields): string
    {
        $fields['cmd'] = self::CMD_NOTIFY_VALIDATE;

        $request = $this->requestFactory
            ->createRequest('POST', $this->getIpnEndpoint())
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($this->streamFactory->createStream(http_build_query($fields)));

        $response = $this->client->sendRequest($request);

        if (! ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        $result = $response->getBody()->getContents();

        return self::NOTIFY_VERIFIED === $result ? self::NOTIFY_VERIFIED : self::NOTIFY_INVALID;
    }

    public function getIpnEndpoint(): string
    {
        return $this->options['sandbox'] ?
            'https://www.sandbox.paypal.com/cgi-bin/webscr' :
            'https://www.paypal.com/cgi-bin/webscr';
    }
}
