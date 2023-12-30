<?php

namespace Payum\Paypal\ProCheckout\Nvp;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\LogicException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 * @see https://www.x.com/sites/default/files/payflowgateway_guide.pdf
 */
class Api
{
    /**
     * Use an AMOUNT of $1000 or less
     * For all processors except Global Payments Central (MAPP) and FDI
     * Credit (C) and force (F) transactions will always be approved regardless of dollar amount or card number
     * @var int
     */
    public const RESULT_SUCCESS = 0;

    /**
     * Use an invalid PWD
     * @var int
     */
    public const RESULT_USER_AUTH_FAIL = 1;

    /**
     * Use an invalid TENDER, such as G
     * @var int
     */
    public const RESULT_INVALID_TENDER = 2;

    /**
     * Use an invalid TRXTYPE, such as G
     * Use the AMOUNT 10402
     * @var int
     */
    public const RESULT_INVALID_TRANSACTION_TYPE = 3;

    /**
     * Use an invalid AMOUNT, such as –1
     * Use any of these as AMOUNT: 10400, 10401, 10403, 10404
     * @var int
     */
    public const RESULT_INVALID_AMOUNT = 4;

    /**
     * Use the AMOUNT1005 - Applies only to the following processors:
     * Global Payments East
     * Global Payments Central
     * American Express
     * Use any of these as AMOUNT: 10548, 10549
     * @var int
     */
    public const RESULT_INVALID_MERCHANT_INFORMATION = 5;

    /**
     * Submit a delayed capture transaction with no ORIGID
     * Use any of these as AMOUNT: 10405, 10406, 10407, 10408, 10409, 10410, 10412, 10413, 10416, 10419, 10420, 10421,
     * 10509, 10512, 10513, 10514, 10515, 10516, 10517, 10518, 10540, 10542
     * @var int
     */
    public const RESULT_FIELD_FORMAT_ERROR = 7;

    /**
     * Use the AMOUNT1012 or an AMOUNT of 2001 or more
     * Use any of these as AMOUNT: 10417, 15002, 15005, 15006, 15028, 15039, 10544, 10545, 10546
     * @var int
     */
    public const RESULT_DECLINED = 12;

    /**
     * Use the AMOUNT1013
     * Use the AMOUNT 10422
     * @var int
     */
    public const RESULT_REFERRAL = 13;

    /**
     * Use any of these as AMOUNT: 10519, 10521, 10522, 10527, 10535, 10541, 10543
     * @var int
     */
    public const RESULT_INVALID_ACCOUNT_NUMBER = 23;

    /**
     * Use any of these as AMOUNT: 10502, 10508
     * @var int
     */
    public const RESULT_INVALID_EXPIRATION_DATE = 24;

    /**
     * Use the AMOUNT 10536
     * @var int
     */
    public const RESULT_DUPLICATE_TRANSACTION = 30;

    /**
     * Attempt to credit an authorization
     * @var int
     */
    public const RESULT_CREDIT_ERROR = 105;

    /**
     * Use the AMOUNT 10505
     * @var int
     */
    public const RESULT_FAILED_AVS_CHECK = 112;

    /**
     * Use the AMOUNT 10504
     * @var int
     */
    public const RESULT_CVV2_MISMATCH = 114;

    // Here more error codes

    /**
     * Fraud Protection Services Filter — Declined by filters
     * @var int
     */
    public const RESULT_DECLINED_BY_FILTERS = 125;

    // Here more error codes

    /**
     * Use an AMOUNT other than those listed in this column
     * @var int
     */
    public const RESULT_GENERIC_HOST_OR_PROCESSOR_ERROR = 1000;

    public const TRXTYPE_SALE = 'S';

    public const TRXTYPE_CREDIT = 'C';

    public const TRXTYPE_AUTHORIZATION = 'A';

    public const TRXTYPE_DELAYED_CAPUTER = 'D';

    public const TRXTYPE_VOID = 'V';

    public const TRXTYPE_VOICE_AUTHORIZATION = 'F';

    public const TRXTYPE_INQUIRY = 'I';

    public const TRXTYPE_DUPLICATE_TRANSACTION = 'N';

    public const TENDER_AUTOMATED_CLEARINGHOUSE = 'A';

    public const TENDER_CREDIT_CARD = 'C';

    public const TENDER_PINLESS_DEBIT = 'D';

    public const TENDER_TELECHECK = 'K';

    public const TENDER_PAYPAL = 'P';

    protected ClientInterface $client;

    protected RequestFactoryInterface $requestFactory;

    protected StreamFactoryInterface $streamFactory;

    /**
     * @var array<string, mixed>|ArrayObject
     */
    protected array | ArrayObject $options = [
        'username' => '',
        'password' => '',
        'partner' => '',
        'vendor' => '',
        'tender' => self::TENDER_CREDIT_CARD,
        'sandbox' => true,
    ];

    /**
     * @param array<string, mixed> $options
     * @throw InvalidArgumentException
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
            'partner',
            'vendor',
            'tender',
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
     * @param array<string, mixed> $fields
     * @return array<string, mixed>
     * @throws ClientExceptionInterface
     */
    public function doSale(array $fields): array
    {
        $fields['TRXTYPE'] = self::TRXTYPE_SALE;
        $this->addAuthorizeFields($fields);

        $result = $this->doRequest($fields);
        $result['TRXTYPE'] = self::TRXTYPE_SALE;

        return $result;
    }

    /**
     * @param array<string, mixed> $fields
     *
     * @return array<string, mixed>
     * @throws ClientExceptionInterface
     */
    public function doCredit(array $fields): array
    {
        $fields['TRXTYPE'] = self::TRXTYPE_CREDIT;
        $this->addAuthorizeFields($fields);

        $result = $this->doRequest($fields);
        $result['TRXTYPE'] = self::TRXTYPE_CREDIT;

        return $result;
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
            ->withBody($this->streamFactory->createStream(http_build_query($fields)));

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
            'https://pilot-payflowpro.paypal.com/' :
            'https://payflowpro.paypal.com/'
        ;
    }

    /**
     * @param array<string, mixed> $fields
     */
    protected function addAuthorizeFields(array &$fields): void
    {
        $fields['USER'] = $this->options['username'];
        $fields['PWD'] = $this->options['password'];
        $fields['PARTNER'] = $this->options['partner'];
        $fields['VENDOR'] = $this->options['vendor'];
        $fields['TENDER'] = $this->options['tender'];
    }
}
