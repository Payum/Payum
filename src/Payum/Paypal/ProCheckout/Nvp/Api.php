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
     * Use the AMOUNT 10402
     * @var int
     */
    const RESULT_INVALID_TRANSACTION_TYPE = 3;

    /**
     * Use an invalid AMOUNT, such as –1
     * Use any of these as AMOUNT: 10400, 10401, 10403, 10404
     * @var int
     */
    const RESULT_INVALID_AMOUNT = 4;

    /**
     * Use the AMOUNT1005 - Applies only to the following processors:
     * Global Payments East
     * Global Payments Central
     * American Express
     * Use any of these as AMOUNT: 10548, 10549
     * @var int
     */
    const RESULT_INVALID_MERCHANT_INFORMATION = 5;

    /**
     * Submit a delayed capture transaction with no ORIGID
     * Use any of these as AMOUNT: 10405, 10406, 10407, 10408, 10409, 10410, 10412, 10413, 10416, 10419, 10420, 10421,
     * 10509, 10512, 10513, 10514, 10515, 10516, 10517, 10518, 10540, 10542
     * @var int
     */
    const RESULT_FIELD_FORMAT_ERROR = 7;

    /**
     * Use the AMOUNT1012 or an AMOUNT of 2001 or more
     * Use any of these as AMOUNT: 10417, 15002, 15005, 15006, 15028, 15039, 10544, 10545, 10546
     * @var int
     */
    const RESULT_DECLINED = 12;

    /**
     * Use the AMOUNT1013
     * Use the AMOUNT 10422
     * @var int
     */
    const RESULT_REFERRAL = 13;

    /**
     * Use any of these as AMOUNT: 10519, 10521, 10522, 10527, 10535, 10541, 10543
     * @var int
     */
    const RESULT_INVALID_ACCOUNT_NUMBER = 23;

    /**
     * Use any of these as AMOUNT: 10502, 10508
     * @var int
     */
    const RESULT_INVALID_EXPIRATION_DATE = 24;

    /**
     * Use the AMOUNT 10536
     * @var int
     */
    const RESULT_DUPLICATE_TRANSACTION = 30;

    /**
     * Attempt to credit an authorization
     * @var int
     */
    const RESULT_CREDIT_ERROR = 105;

    /**
     * Use the AMOUNT 10505
     * @var int
     */
    const RESULT_FAILED_AVS_CHECK = 112;

    /**
     * Use the AMOUNT 10504
     * @var int
     */
    const RESULT_CVV2_MISMATCH = 114;

    // Here more error codes

    /**
     * Fraud Protection Services Filter — Declined by filters
     * @var int
     */
    const RESULT_DECLINED_BY_FILTERS = 125;

    // Here more error codes

    /**
     * Use an AMOUNT other than those listed in this column
     * @var int
     */
    const RESULT_GENERIC_HOST_OR_PROCESSOR_ERROR = 1000;

    // Here more error codes too

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
            throw new HttpResponseNotSuccessException($request, $response, "", $response['RESULT']);
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

    /**
     * @return array
     */
    public static function getResultErrorCodes()
    {
        $return = array();
        $refl = new \ReflectionClass(get_called_class());
        foreach ($refl->getConstants() as $key => $val) {
            if ('RESULT_' == substr($key, 0, strlen('RESULT_'))) {
                $return[$key] = $val;
            }
        }
        return $return;
    }
}
