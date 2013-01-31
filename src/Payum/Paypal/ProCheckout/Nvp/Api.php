<?php
namespace Payum\Paypal\ProCheckout\Nvp;

use Buzz\Client\ClientInterface;
use Payum\Exception\Http\HttpResponseStatusNotSuccessfulException;
use Payum\Paypal\ProCheckout\Nvp\Bridge\Buzz\Request;
use Payum\Paypal\ProCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ProCheckout\Nvp\Exception\Http\HttpResponseNotSuccessException;
use Payum\Exception\InvalidArgumentException;

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
    const RESULT_SUCCESS = 0;

    /**
     * Use an invalid PWD
     * @var int
     */
    const RESULT_USER_AUTH_FAIL = 1;

    /**
     * Use an invalid TENDER, such as G
     * @var int
     */
    const RESULT_INVALID_TENDER = 2;

    /**
     * Use an invalid TRXTYPE, such as G
     * @var int
     */
    const RESULT_INVALID_TRANSACTION_TYPE = 3;

    /**
     * Use an invalid AMOUNT, such as –1
     * @var int
     */
    const RESULT_INVALID_AMOUNT = 4;

    /**
     * Use the AMOUNT1005 - Applies only to the following processors:
     * Global Payments East
     * Global Payments Central
     * American Express
     * @var int
     */
    const RESULT_INVALID_MERCHANT_INFORMATION = 5;

    /**
     * Submit a delayed capture transaction with no ORIGID
     * @var int
     */
    const RESULT_FIELD_FORMAT_ERROR = 7;

    /**
     * Use the AMOUNT1012 or an AMOUNT of 2001 or more
     * @var int
     */
    const RESULT_DECLINED = 12;

    /**
     * Use the AMOUNT1013
     * @var int
     */
    const RESULT_REFERRAL = 13;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var array
     */
    protected $options = array(
        'username' => null,
        'password' => null,
        'partner' => null,
        'vendor' => null,
        'tender' => 'C',
        'trxtype' => 'S',
        'sandbox' => null,
    );

    /**
     * @param ClientInterface $client
     * @param array $options
     *
     * @throw InvalidArgumentException
     */
    public function __construct(ClientInterface $client, array $options)
    {
        $this->client = $client;
        $this->options = array_replace($this->options, $options);
        
        if (true == empty($this->options['username'])) {
            throw new InvalidArgumentException('The username option must be set.');
        }
        if (true == empty($this->options['password'])) {
            throw new InvalidArgumentException('The password option must be set.');
        }
        if (true == empty($this->options['partner'])) {
            throw new InvalidArgumentException('The partner option must be set.');
        }
        if (true == empty($this->options['vendor'])) {
            throw new InvalidArgumentException('The vendor option must be set.');
        }
        if (false == is_bool($this->options['sandbox'])) {
            throw new InvalidArgumentException('The boolean sandbox option must be set.');
        }
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function doPayment(Request $request)
    {
        $this->addOptions($request);

        return $this->doRequest($request);
    }

    /**
     * @param Request $request
     *
     * @throws HttpResponseStatusNotSuccessfulException
     * @throws HttpResponseNotSuccessException
     *
     * @return Response
     */
    protected function doRequest(Request $request)
    {
        $request->setMethod('POST');
        $request->fromUrl($this->getApiEndpoint());
        $this->client->send($request, $response = $this->createResponse());


        if (false == $response->isSuccessful()) {
            throw new HttpResponseStatusNotSuccessfulException($request, $response);
        }

        if (self::RESULT_SUCCESS != $response['RESULT']) {
            throw new HttpResponseNotSuccessException($request, $response);
        }

        return $response;
    }

    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        $host = $this->options['sandbox'] ? 'pilot-payflowpro.paypal.com' : 'payflowpro.paypal.com';

        return sprintf(
            'https://%s/',
            $host
        );
    }

    /**
     * @param Request $request
     */
    protected function addOptions(Request $request)
    {
        $request->setField('USER', $this->options['username']);
        $request->setField('PWD', $this->options['password']);
        $request->setField('PARTNER', $this->options['partner']);
        $request->setField('VENDOR', $this->options['vendor']);
        $request->setField('TENDER', $this->options['tender']);
        $request->setField('TRXTYPE', $this->options['trxtype']);
    }

    /**
     * @return Response
     */
    protected function createResponse()
    {
        return new Response();
    }
}
