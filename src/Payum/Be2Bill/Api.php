<?php
namespace Payum\Be2Bill;

use Buzz\Client\ClientInterface;
use Buzz\Message\Form\FormRequest;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Bridge\Buzz\JsonResponse;

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
     * @var \Buzz\Client\ClientInterface
     */
    protected $client;

    /**
     * @var array
     */
    protected $options = array(
        'identifier' => null,
        'password' => null,
        'sandbox' => null,
    );

    /**
     * @param \Buzz\Client\ClientInterface $client
     * @param array $options
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(ClientInterface $client, array $options)
    {
        $this->client = $client;
        $this->options = array_replace($this->options, $options);

        if (true == empty($this->options['identifier'])) {
            throw new InvalidArgumentException('The identifier option must be set.');
        }
        if (true == empty($this->options['password'])) {
            throw new InvalidArgumentException('The password option must be set.');
        }
        if (false == is_bool($this->options['sandbox'])) {
            throw new InvalidArgumentException('The boolean sandbox option must be set.');
        }
    }

    /**
     * @param array $params
     *
     * @return \Payum\Core\Bridge\Buzz\JsonResponse
     */
    public function payment(array $params)
    {
        $request = new FormRequest();

        $params['OPERATIONTYPE'] = static::OPERATION_PAYMENT;
        $params = $this->appendGlobalParams($params);

        $request->setField('method', 'payment');
        $request->setField('params', $params);

        return $this->doRequest($request);
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
     * @param \Buzz\Message\Form\FormRequest $request
     *
     * @throws \Payum\Core\Exception\Http\HttpException
     *
     * @return \Payum\Core\Bridge\Buzz\JsonResponse
     */
    protected function doRequest(FormRequest $request)
    {
        $request->setMethod('POST');
        $request->fromUrl($this->getApiEndpoint());

        $this->client->send($request, $response = new JsonResponse());

        if (false == $response->isSuccessful()) {
            throw HttpException::factory($request, $response);
        }

        return $response;
    }

    /**
     * @return string
     */
    public function getOnsiteUrl()
    {
        return $this->options['sandbox'] ?
            'https://secure-test.be2bill.com/front/form/process' :
            'https://secure-magenta1.be2bill.com/front/form/process'
        ;
    }

    /**
     * @param array $params
     * @return array
     */
    public function prepareOnsitePayment(array $params)
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
        $params = $this->appendGlobalParams($params);

        return $params;
    }

    /**
     * @param array $params
     * @return array
     */
    protected function appendGlobalParams(array $params = array())
    {
        $params['VERSION'] = self::VERSION;
        $params['IDENTIFIER'] = $this->options['identifier'];
        $params['HASH'] = $this->calculateHash($params);

        return $params;
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
    protected function calculateHash(array $params)
    {
        #Alpha sort
        ksort($params);

        $clearString = $this->options['password'];
        foreach ($params as $key => $value) {
            $clearString .= $key . '=' . $value . $this->options['password'];
        }

        return hash('sha256', $clearString);
    }
}
