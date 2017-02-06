<?php
namespace Payum\Be2Bill;

use Http\Message\MessageFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\LogicException;
use Payum\Core\HttpClientInterface;

class Api
{
    const VERSION = '2.0';

    const EXECCODE_SUCCESSFUL = '0000';

    const EXECCODE_3DSECURE_IDENTIFICATION_REQUIRED = '0001';

    const EXECCODE_PARAMETER_X_MISSING = '1001';

    const EXECCODE_INVALID_PARAMETER_X = '1002';

    const EXECCODE_HASH_ERROR = '1003';

    const EXECCODE_UNSUPPORTED_PROTOCOL = '1004';

    const EXECCODE_ALIAS_NOT_FOUND = '2001';

    const EXECCODE_UNSUCCESSFUL_REFERENCE_TRANSACTION = '2002';

    const EXECCODE_NON_REFUNDABLE_REFERENCE_TRANSACTION = '2003';

    const EXECCODE_REFERENCE_TRANSACTION_NOT_FOUND = '2004';

    const EXECCODE_NOT_ABLE_TO_CAPTURE_THE_REFERENCE_AUTHORIZATION = '2005';

    const EXECCODE_UNFINISHED_REFERENCE_TRANSACTION = '2006';

    const EXECCODE_INVALID_CAPTURE_AMOUNT = '2007';

    const EXECCODE_ACCOUNT_DEACTIVATED = '3001';

    const EXECCODE_UNAUTHORIZED_SERVER_IP_ADDRESS = '3002';

    const EXECCODE_UNAUTHORIZED_TRANSACTION = '3003';

    const EXECCODE_TRANSACTION_REFUSED_BY_THE_BANK = '4001';

    const EXECCODE_INSUFFICIENT_FUNDS = '4002';

    const EXECCODE_CARD_REFUSED_BY_THE_BANK = '4003';

    const EXECCODE_ABORTED_TRANSACTION = '4004';

    const EXECCODE_SUSPECTED_FRAUD = '4005';

    const EXECCODE_CARD_LOST = '4006';

    const EXECCODE_STOLEN_CARD = '4007';

    const EXECCODE_3DSECURE_AUTHENTICATION_FAILED = '4008';

    const EXECCODE_EXPIRED_3DSECURE_AUTHENTICATION = '4009';

    const EXECCODE_INTERNAL_ERROR = '5001';

    const EXECCODE_BANK_ERROR = '5002';

    const EXECCODE_UNDERGOING_MAINTENANCE = '5003';

    const EXECCODE_TIME_OUT = '5004';

    /**
     * The "payment" function is the basic function that allows collecting from a cardholder.
     * This operation collects money directly.
     */
    const OPERATION_PAYMENT = 'payment';

    /**
     * The "authorization" function allows "freezing" temporarily the funds in a cardholder's bank
     * account for 7 days. This application does not debit it.
     * This type of operation is mainly used in the world of physical goods ("retail") when the merchant
     * decides to debit his customer at merchandise shipping time.
     */
    const OPERATION_AUTHORIZATION = 'authorization';

    /**
     * The "capture" function allows collecting funds from a cardholder after an authorization
     * ("authorization" function). This capture can take place within 7 days after the authorization.
     */
    const OPERATION_CAPTURE = 'capture';

    /**
     * This dual function is directly managed by the system:
     * - Refund: Consists of returning the already collected funds to a cardholder
     * - Cancellation: Consists of not sending a payment transaction as compensation
     */
    const OPERATION_REFUND = 'refund';

    /**
     * The "credit" function allows sending funds to a cardholder.
     */
    const OPERATION_CREDIT = 'credit';

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
        'identifier' => null,
        'password' => null,
        'sandbox' => null,
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
        $options->validateNotEmpty(array(
            'identifier',
            'password',
        ));

        if (false == is_bool($options['sandbox'])) {
            throw new LogicException('The boolean sandbox option must be set.');
        }

        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function payment(array $params)
    {
        $params['OPERATIONTYPE'] = static::OPERATION_PAYMENT;

        $this->addGlobalParams($params);

        return $this->doRequest([
            'method' => 'payment',
            'params' => $params
        ]);
    }

    /**
     * Verify if the hash of the given parameter is correct
     *
     * @param array $params
     *
     * @return bool
     */
    public function verifyHash(array $params)
    {
        if (empty($params['HASH'])) {
            return false;
        }

        $hash = $params['HASH'];
        unset($params['HASH']);

        return $hash === $this->calculateHash($params);
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function doRequest(array $fields)
    {
        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
        );

        $request = $this->messageFactory->createRequest('POST', $this->getApiEndpoint(), $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        $result = json_decode($response->getBody()->getContents());
        if (null === $result) {
            throw new LogicException("Response content is not valid json: \n\n{$response->getBody()->getContents()}");
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getOffsiteUrl()
    {
        return $this->options['sandbox'] ?
            'https://secure-test.be2bill.com/front/form/process.php' :
            'https://secure-magenta1.be2bill.com/front/form/process.php'
        ;
    }

    /**
     * @param  array $params
     *
     * @return array
     */
    public function prepareOffsitePayment(array $params)
    {
        $supportedParams = array(
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
        );

        $params = array_filter(array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        ));

        $params['OPERATIONTYPE'] = static::OPERATION_PAYMENT;

        $this->addGlobalParams($params);

        return $params;
    }

    /**
     * @param  array $params
     */
    protected function addGlobalParams(array &$params)
    {
        $params['VERSION'] = self::VERSION;
        $params['IDENTIFIER'] = $this->options['identifier'];
        $params['HASH'] = $this->calculateHash($params);
    }
    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        return $this->options['sandbox'] ?
            'https://secure-test.be2bill.com/front/service/rest/process' :
            'https://secure-magenta1.be2bill.com/front/service/rest/process'
        ;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function calculateHash(array $params)
    {
        #Alpha sort
        ksort($params);

        $clearString = $this->options['password'];
        foreach ($params as $key => $value) {
            $clearString .= $key.'='.$value.$this->options['password'];
        }

        return hash('sha256', $clearString);
    }
}
