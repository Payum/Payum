<?php

namespace Payum\Be2Bill;

use JsonException;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\LogicException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Api
{
    public const VERSION = '2.0';

    public const EXECCODE_SUCCESSFUL = '0000';

    public const EXECCODE_3DSECURE_IDENTIFICATION_REQUIRED = '0001';

    public const EXECCODE_PARAMETER_X_MISSING = '1001';

    public const EXECCODE_INVALID_PARAMETER_X = '1002';

    public const EXECCODE_HASH_ERROR = '1003';

    public const EXECCODE_UNSUPPORTED_PROTOCOL = '1004';

    public const EXECCODE_ALIAS_NOT_FOUND = '2001';

    public const EXECCODE_UNSUCCESSFUL_REFERENCE_TRANSACTION = '2002';

    public const EXECCODE_NON_REFUNDABLE_REFERENCE_TRANSACTION = '2003';

    public const EXECCODE_REFERENCE_TRANSACTION_NOT_FOUND = '2004';

    public const EXECCODE_NOT_ABLE_TO_CAPTURE_THE_REFERENCE_AUTHORIZATION = '2005';

    public const EXECCODE_UNFINISHED_REFERENCE_TRANSACTION = '2006';

    public const EXECCODE_INVALID_CAPTURE_AMOUNT = '2007';

    public const EXECCODE_ACCOUNT_DEACTIVATED = '3001';

    public const EXECCODE_UNAUTHORIZED_SERVER_IP_ADDRESS = '3002';

    public const EXECCODE_UNAUTHORIZED_TRANSACTION = '3003';

    public const EXECCODE_TRANSACTION_REFUSED_BY_THE_BANK = '4001';

    public const EXECCODE_INSUFFICIENT_FUNDS = '4002';

    public const EXECCODE_CARD_REFUSED_BY_THE_BANK = '4003';

    public const EXECCODE_ABORTED_TRANSACTION = '4004';

    public const EXECCODE_SUSPECTED_FRAUD = '4005';

    public const EXECCODE_CARD_LOST = '4006';

    public const EXECCODE_STOLEN_CARD = '4007';

    public const EXECCODE_3DSECURE_AUTHENTICATION_FAILED = '4008';

    public const EXECCODE_EXPIRED_3DSECURE_AUTHENTICATION = '4009';

    public const EXECCODE_INTERNAL_ERROR = '5001';

    public const EXECCODE_BANK_ERROR = '5002';

    public const EXECCODE_UNDERGOING_MAINTENANCE = '5003';

    public const EXECCODE_TIME_OUT = '5004';

    /**
     * The "payment" function is the basic function that allows collecting from a cardholder.
     * This operation collects money directly.
     */
    public const OPERATION_PAYMENT = 'payment';

    /**
     * The "authorization" function allows "freezing" temporarily the funds in a cardholder's bank
     * account for 7 days. This application does not debit it.
     * This type of operation is mainly used in the world of physical goods ("retail") when the merchant
     * decides to debit his customer at merchandise shipping time.
     */
    public const OPERATION_AUTHORIZATION = 'authorization';

    /**
     * The "capture" function allows collecting funds from a cardholder after an authorization
     * ("authorization" function). This capture can take place within 7 days after the authorization.
     */
    public const OPERATION_CAPTURE = 'capture';

    /**
     * This dual function is directly managed by the system:
     * - Refund: Consists of returning the already collected funds to a cardholder
     * - Cancellation: Consists of not sending a payment transaction as compensation
     */
    public const OPERATION_REFUND = 'refund';

    /**
     * The "credit" function allows sending funds to a cardholder.
     */
    public const OPERATION_CREDIT = 'credit';

    protected ClientInterface $client;

    protected RequestFactoryInterface $requestFactory;

    protected StreamFactoryInterface $streamFactory;

    /**
     * @var array<string, mixed>|ArrayObject
     */
    protected array | ArrayObject $options = [
        'identifier' => null,
        'password' => null,
        'sandbox' => null,
    ];

    /**
     * @param array<string, mixed> $options
     * @throws InvalidArgumentException if an option is invalid
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
            'identifier',
            'password',
        ]);

        if (! is_bool($options['sandbox'])) {
            throw new LogicException('The boolean sandbox option must be set.');
        }

        $this->options = $options;
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     * @throws JsonException
     */
    public function payment(array $params): array
    {
        $params['OPERATIONTYPE'] = static::OPERATION_PAYMENT;

        $this->addGlobalParams($params);

        return $this->doRequest([
            'method' => 'payment',
            'params' => $params,
        ]);
    }

    /**
     * Verify if the hash of the given parameter is correct
     *
     * @param array<string, mixed> $params
     */
    public function verifyHash(array $params): bool
    {
        if (empty($params['HASH'])) {
            return false;
        }

        $hash = $params['HASH'];
        unset($params['HASH']);

        return $hash === $this->calculateHash($params);
    }

    public function getOffsiteUrl(): string
    {
        return $this->options['sandbox'] ?
            'https://secure-test.be2bill.com/front/form/process.php' :
            'https://secure-magenta1.be2bill.com/front/form/process.php'
        ;
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function prepareOffsitePayment(array $params): array
    {
        $supportedParams = [
            'CLIENTIDENT' => null,
            'DESCRIPTION' => null,
            'ORDERID' => null,
            'AMOUNT' => null,
            'CARDTYPE' => null,
            'CLIENTEMAIL' => null,
            'CARDFULLNAME' => null,
            'LANGUAGE' => null,
            'EXTRADATA' => null,
            'CLIENTDOB' => null,
            'CLIENTADDRESS' => null,
            'CREATEALIAS' => null,
            '3DSECURE' => null,
            '3DSECUREDISPLAYMODE' => null,
            'USETEMPLATE' => null,
            'HIDECLIENTEMAIL' => null,
            'HIDEFULLNAME' => null,
        ];

        $params = array_filter(array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        ));

        $params['OPERATIONTYPE'] = static::OPERATION_PAYMENT;

        $this->addGlobalParams($params);

        return $params;
    }

    /**
     * @param array<string, mixed> $params
     */
    public function calculateHash(array $params): string
    {
        #Alpha sort
        ksort($params);

        $clearString = $this->options['password'];
        foreach ($params as $key => $value) {
            $clearString .= $key . '=' . $value . $this->options['password'];
        }

        return hash('sha256', (string) $clearString);
    }

    /**
     * @param array<string, mixed> $fields
     * @return array<string, mixed>
     * @throws JsonException
     */
    protected function doRequest(array $fields): array
    {
        $request = $this->requestFactory
            ->createRequest('POST', $this->getApiEndpoint())
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($this->streamFactory->createStream(http_build_query($fields['params'])))
        ;

        $response = $this->client->sendRequest($request);

        if (! ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        $result = json_decode($response->getBody()->getContents(), null, 512, JSON_THROW_ON_ERROR);
        if (null === $result) {
            throw new LogicException("Response content is not valid json: \n\n{$response->getBody()->getContents()}");
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $params
     */
    protected function addGlobalParams(array &$params): void
    {
        $params['VERSION'] = self::VERSION;
        $params['IDENTIFIER'] = $this->options['identifier'];
        $params['HASH'] = $this->calculateHash($params);
    }

    protected function getApiEndpoint(): string
    {
        return $this->options['sandbox'] ?
            'https://secure-test.be2bill.com/front/service/rest/process' :
            'https://secure-magenta1.be2bill.com/front/service/rest/process'
        ;
    }
}
