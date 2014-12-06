<?php
namespace Payum\Paypal\ProCheckout\Nvp;

use Buzz\Client\ClientInterface;
use Buzz\Message\Form\FormRequest;
use Buzz\Message\Request;
use Buzz\Message\Response;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;

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

    const TRXTYPE_SALE = 'S';

    const TRXTYPE_CREDIT = 'C';

    const TRXTYPE_AUTHORIZATION = 'A';

    const TRXTYPE_DELAYED_CAPUTER = 'D';

    const TRXTYPE_VOID = 'V';

    const TRXTYPE_VOICE_AUTHORIZATION = 'F';

    const TRXTYPE_INQUIRY = 'I';

    const TRXTYPE_DUPLICATE_TRANSACTION = 'N';

    const TENDER_AUTOMATED_CLEARINGHOUSE = 'A';

    const TENDER_CREDIT_CARD = 'C';

    const TENDER_PINLESS_DEBIT = 'D';

    const TENDER_TELECHECK = 'K';

    const TENDER_PAYPAL = 'P';

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var array
     */
    protected $options = array(
        'username' => '',
        'password' => '',
        'partner' => '',
        'vendor' => '',
        'tender' => self::TENDER_CREDIT_CARD,
        'sandbox' => true,
    );

    /**
     * @param ClientInterface $client
     * @param array $options
     *
     * @throw InvalidArgumentException
     */
    public function __construct(array $options, ClientInterface $client = null)
    {
        $this->client = $client;
        $this->options = array_replace($this->options, $options);

        ArrayObject::ensureArrayObject($this->options)->validateNotEmpty(array(
            'username',
            'password',
            'partner',
            'vendor',
            'tender'
        ));
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    public function doSale(array $fields)
    {
        $request = new FormRequest();
        $request->setFields($fields);
        $request->setField('TRXTYPE', self::TRXTYPE_SALE);

        $this->addOptions($request);

        $result = $this->doRequest($request);
        $result['TRXTYPE'] = self::TRXTYPE_SALE;

        return $result;
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    public function doCredit(array $fields)
    {
        $request = new FormRequest();
        $request->setFields($fields);
        $request->setField('TRXTYPE', self::TRXTYPE_CREDIT);

        $this->addOptions($request);

        $result = $this->doRequest($request);
        $result['TRXTYPE'] = self::TRXTYPE_CREDIT;

        return $result;
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
        $request->fromUrl($this->getApiEndpoint());

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
     * @param FormRequest $request
     */
    protected function addOptions(FormRequest $request)
    {
        $request->setField('USER', $this->options['username']);
        $request->setField('PWD', $this->options['password']);
        $request->setField('PARTNER', $this->options['partner']);
        $request->setField('VENDOR', $this->options['vendor']);
        $request->setField('TENDER', $this->options['tender']);
        $request->setField('BUTTONSOURCE', 'AngellEYE_SP_Payum');
    }

    /**
     * @return Response
     */
    protected function createResponse()
    {
        return new Response();
    }
}
