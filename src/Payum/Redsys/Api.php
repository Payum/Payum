<?php
/**
 * Created by PhpStorm.
 * User: carlos
 * Date: 1/11/14
 * Time: 13:55
 */

namespace Payum\Redsys;

use Buzz\Client\ClientInterface;
use Buzz\Client\Curl;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Model\OrderInterface;
use Payum\Core\Security\TokenInterface;

class Api
{
    protected $options = array(
        'merchant_code' => null,
        'terminal' => null,
        'secret_key' => null,
        'url' => null
    );

    /**
     * Currency codes to the values the bank
     * understand. Remember you can only work
     * with one of them per commerce
     */
    protected $currencies = array(
        'EUR' => '978',
        'USD' => '840',
        'GBP' => '826',
        'JPY' => '392',
        'ARA' => '32',
        'CAD' => '124',
        'CLP' => '152',
        'COP' => '170',
        'INR' => '356',
        'MXN' => '484',
        'PEN' => '604',
        'CHF' => '756',
        'BRL' => '986',
        'VEF' => '937',
        'TRL' => '949'
    );

    public function __construct( array $options, ClientInterface $client = null )
    {
        $this->client = $client ?: new Curl;

        $this->options = array_replace( $this->options, $options );

        if (true == empty( $this->options['merchant_code'] )) {
            throw new InvalidArgumentException( 'The merchant_code option must be set.' );
        }
        if (true == empty( $this->options['terminal'] )) {
            throw new InvalidArgumentException( 'The terminal option must be set.' );
        }
        if (true == empty( $this->options['secret_key'] )) {
            throw new InvalidArgumentException( 'The secret_key option must be set.' );
        }
        if (false == is_bool($this->options['sandbox'])) {
            throw new InvalidArgumentException('The boolean sandbox option must be set.');
        }
    }

    /**
     * Returns the url where we need to send the post request
     *
     * @return string
     */
    public function getRedsysUrl()
    {
        return $this->options['sanbox']
            ? 'https://sis-t.redsys.es:25443/canales'
            : 'https://sis.redsys.es/canales/';
    }

    /**
     * Prepare the payment depending on the order and the token
     *
     * @param OrderInterface $order
     * @param TokenInterface $token
     *
     * @return array
     */
    public function preparePayment( OrderInterface $order, TokenInterface $token )
    {
        $details = $order->getDetails();

        $details['Ds_Merchant_Amount'] = $order->getTotalAmount();

        $details['Ds_Merchant_Currency'] = $this->currencies[$order->getCurrencyCode()];

        $details['Ds_Merchant_Order'] = $this->ensureCorrectOrderNumber( $order->getNumber() );

        // following values can be addded to the details 
        // order when building it. If they are not passed, values
        // will be taken from the default options if present
        // in case of Ds_Merchant_TransactionType, as its mandatory
        // 0 will be asigned in case value is not present in the 
        // order details or in the options. 
        if (!isset( $details['Ds_Merchant_TransactionType'] )) {
            $details['Ds_Merchant_TransactionType'] = isset( $this->options['default_transaction_type'] )
                ? $this->options['default_transaction_type'] : 0;
        }

        // set customer language to spanish in case not provided
        if (!isset( $details['Ds_Merchant_ConsumerLanguage'] )) {
            $details['Ds_Merchant_ConsumerLanguage'] = '001';
        }

        // these following to are not mandatory. only filled if present in the 
        // order details or in the options
        if (!isset( $details['Ds_Merchant_MerchantName'] ) && isset( $this->options['merchant_name'] )) {
            $details['Ds_Merchant_MerchantName'] = $this->options['merchant_name'];
        }
        if (!isset( $details['Ds_Merchant_ProductDescription'] ) && isset( $this->options['product_description'] )) {
            $details['Ds_Merchant_ProductDescription'] = $this->options['product_description'];
        }

        // notification url where the bank will post the response        
        $details['Ds_Merchant_MerchantURL'] = $token->getTargetUrl();

        // return url in case of payment done
        $details['Ds_Merchant_UrlOK'] = $token->getAfterUrl();

        // return url in case of payment cancel. same as above
        $details['Ds_Merchant_UrlKO'] = $token->getAfterUrl();
        $details['Ds_Merchant_MerchantSignature'] = $this->signature( $details );

        return $details;
    }

    /**
     * Validate the order number passed to the bank. it needs to pass the
     * following test
     *
     * - Must be between 4 and 12 characters
     *     - We complete with 0 to the left in case length or the number is lower
     *       than 4 in order to make the integration easier
     * - Four first characters must be digits
     * - Following eight can be digits or characters which ASCII numbers are:
     *    - between 65 and 90 ( A - Z)
     *    - between 97 and 122 ( a - z )
     *
     * If the test pass, orderNumber will be returned. if not, a Exception will be thrown
     *
     * @param string $orderNumber
     *
     * @return string
     */
    private function ensureCorrectOrderNumber( $orderNumber )
    {
        // add 0 to the left in case length of the order number is less than 4
        $orderNumber = str_pad( $orderNumber, 4, '0', STR_PAD_LEFT );

        $firstPartOfTheOrderNumber = substr( $orderNumber, 0, 4 );
        $secondPartOfTheOrderNumber = substr( $orderNumber, 4, strlen( $orderNumber ) );

        if (!ctype_digit( $firstPartOfTheOrderNumber ) ||
            !ctype_alnum( $secondPartOfTheOrderNumber )
        ) {
            throw new InvalidArgumentException( 'The order number is not correct.' );
        }

        return $orderNumber;
    }

    /**
     * Calculate the signature depending on some other values
     * sent in the payment.
     *
     * @param array $params
     *
     * @return string
     */
    private function signature( $params )
    {
        $msgToSign = $params['Ds_Merchant_Amount']
            . $params['Ds_Merchant_Order']
            . $this->options['merchant_code']
            . $params['Ds_Merchant_Currency']
            . $params['Ds_Merchant_TransactionType']
            . $params['Ds_Merchant_MerchantURL']
            . $this->options['secret_key'];

        return strtoupper( sha1( $msgToSign ) );
    }

    /**
     * Adds merchant code and merchant terminal to the payment built
     * in the fillorderdetails action
     */
    public function addMerchantDataToPayment( array $payment )
    {
        $payment['Ds_Merchant_MerchantCode'] = $this->options['merchant_code'];

        $payment['Ds_Merchant_Terminal'] = $this->options['terminal'];

        return $payment;
    }

    /**
     * Validate the response to be sure the bank is sending it
     *
     * @param array $response
     *
     * @return bool
     */
    public function validateGatewayResponse( $response )
    {
        $msgToSign = $response['Ds_Amount']
            . $response['Ds_Order']
            . $this->options['merchant_code']
            . $response['Ds_Currency']
            . $response['Ds_Response']
            . $this->options['secret_key'];

        return strtoupper( sha1( $msgToSign ) ) == $response['Ds_Signature'];
    }
}
