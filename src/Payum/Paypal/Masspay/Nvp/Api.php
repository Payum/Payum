<?php

namespace Payum\Paypal\Masspay\Nvp;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\InvalidArgumentException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Api
{
    public const VERSION = '2.3';

    public const ACK_SUCCESS = 'Success';

    public const ACK_SUCCESS_WITH_WARNING = 'SuccessWithWarning';

    public const ACK_FAILURE = 'Failure';

    public const ACK_FAILURE_WITH_WARNING = 'FailureWithWarning';

    protected ClientInterface $client;

    protected StreamFactoryInterface $streamFactory;

    protected RequestFactoryInterface $requestFactory;

    /**
     * @var array<string, mixed>|ArrayObject
     */
    protected array | ArrayObject $options = [
        'username' => null,
        'password' => null,
        'signature' => null,
    ];

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        array $options,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
    ) {
        $options = ArrayObject::ensureArrayObject($options);
        $options->defaults($this->options);
        $options->validateNotEmpty([
            'username',
            'password',
            'signature',
        ]);

        if (! is_bool($options['sandbox'])) {
            throw new InvalidArgumentException('The boolean sandbox option must be set.');
        }

        $this->options = $options;
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * @param array<string, mixed> $fields
     * @return array<string, mixed>
     * @throws ClientExceptionInterface
     */
    public function massPay(array $fields): array
    {
        $fields['METHOD'] = 'MassPay';

        $this->addVersionField($fields);
        $this->addAuthorizeFields($fields);

        return $this->doRequest($fields);
    }

    /**
     * @param array<string, mixed> $fields
     * @return array<string, mixed>
     * @throws ClientExceptionInterface
     */
    protected function doRequest(array $fields): array
    {
        $request = $this->requestFactory
            ->createRequest('POST', $this->getApiEndpoint())
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($this->streamFactory->createStream(http_build_query($fields)))
        ;

        $response = $this->client->sendRequest($request);

        if (! ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        $result = [];
        parse_str($response->getBody()->getContents(), $result);
        foreach ($result as &$value) {
            $value = urldecode($value);
        }

        return $result;
    }

    protected function getApiEndpoint(): string
    {
        return $this->options['sandbox'] ?
            'https://api-3t.sandbox.paypal.com/nvp' :
            'https://api-3t.paypal.com/nvp'
        ;
    }

    /**
     * @param array<string, mixed> $fields
     */
    protected function addAuthorizeFields(array &$fields): void
    {
        $fields['PWD'] = $this->options['password'];
        $fields['USER'] = $this->options['username'];
        $fields['SIGNATURE'] = $this->options['signature'];
    }

    /**
     * @param array<string, mixed> $fields
     */
    protected function addVersionField(array &$fields): void
    {
        $fields['VERSION'] = self::VERSION;
    }
}
