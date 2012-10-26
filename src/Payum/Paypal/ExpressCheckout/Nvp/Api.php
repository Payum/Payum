<?php
namespace Payum\Paypal\ExpressCheckout\Nvp;

use Buzz\Client\ClientInterface;
use Buzz\Message\Form\FormRequest;

use Payum\Exception\Http\HttpResponseNotSuccessfulException;
use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;

/**
 * Docs:
 *   L_ERRORCODE: https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_errorcodes
 *   ACK: https://www.x.com/content/paypal-nvp-api-overview
 *   CHECKOUTSTATUS: https://www.x.com/developers/paypal/documentation-tools/api/getexpresscheckoutdetails-api-operation-nvp
 *   PAYMENTSTATUS: https://www.x.com/developers/paypal/documentation-tools/api/doexpresscheckoutpayment-api-operation-nvp
 */
class Api
{
    const ACK_SUCCESS = 'Success';
    
    const ACK_SUCCESS_WITH_WARNING = 'SuccessWithWarning';
    
    const ACK_FAILURE = 'Failure';
    
    const ACK_FAILUREWITHWARNING = 'FailureWithWarning';
    
    const ACK_WARNING = 'Warning';
    
    const CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED = 'PaymentActionNotInitiated';
        
    const CHECKOUTSTATUS_PAYMENT_ACTION_FAILED = 'PaymentActionFailed';
        
    const CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS = 'PaymentActionInProgress';
        
    const CHECKOUTSTATUS_PAYMENT_COMPLETED = 'PaymentCompleted';

    /**
     * No status
     */
    const PAYMENTSTATUS_NONE = 'None';

    /**
     * A reversal has been canceled; for example, when you win a dispute and the funds for the reversal have been returned to you.
     */
    const PAYMENTSTATUS_CANCELED_REVERSAL = 'Canceled-Reversal';

    /**
     * The payment has been completed, and the funds have been added successfully to your account balance.
     */
    const PAYMENTSTATUS_COMPLETED = 'Completed';

    /**
     * You denied the payment. This happens only if the payment was previously pending because of possible reasons described for the PendingReason element.
     */
    const PAYMENTSTATUS_DENIED = 'Denied';

    /**
     * The authorization period for this payment has been reached.
     */
    const PAYMENTSTATUS_EXPIRED = 'Expired';

    /**
     * The payment has failed. This happens only if the payment was made from your buyer's bank account.
     */
    const PAYMENTSTATUS_FAILED = 'Failed';

    /**
     * The transaction has not terminated, e.g. an authorization may be awaiting completion.
     */
    const PAYMENTSTATUS_IN_PROGRESS = 'In-Progress';

    /**
     * The payment has been partially refunded. 
     */
    const PAYMENTSTATUS_PARTIALLY_REFUNDED = 'Partially-Refunded';

    /**
     * The payment is pending. See the PendingReason field for more information.
     */
    const PAYMENTSTATUS_PENDING = 'Pending';

    /**
     * You refunded the payment.
     */
    const PAYMENTSTATUS_REFUNDED = 'Refunded';

    /**
     * A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer. The reason for the reversal is specified in the ReasonCode element.
     */
    const PAYMENTSTATUS_REVERSED = 'Reversed';

    /**
     *  A payment has been accepted.
     */
    const PAYMENTSTATUS_PROCESSED = 'Processed';

    /**
     * An authorization for this transaction has been voided.
     */
    const PAYMENTSTATUS_VOIDED = 'Voided';

    /**
     * The payment has been completed, and the funds have been added successfully to your pending balance.
     */
    const PAYMENTSTATUS_COMPLETED_FUNDS_HELD = 'Completed-Funds-Held';

    /**
     * Payment has not been authorized by the user.
     */
    const L_ERRORCODE_PAYMENT_NOT_AUTHORIZED = 10485;
    
    const VERSION = '65.1';

    protected $password;

    protected $username;

    protected $signature;

    protected $returnUrl;

    protected $cancelUrl;

    protected $debug;

    protected $client;

    public function __construct(ClientInterface $client, $username, $password, $signature, $returnUrl, $cancelUrl, $debug)
    {
        $this->client = $client;
        $this->username = $username;
        $this->password = $password;
        $this->signature = $signature;
        $this->returnUrl = $returnUrl;
        $this->cancelUrl = $cancelUrl;

        $this->debug = (boolean) $debug;
    }
    
    /**
     * Require: PAYMENTREQUEST_0_AMT
     * 
     * @param array $fields
     *
     * @return Respons
     */
    public function setExpressCheckout(FormRequest $request)
    {
        $request->setField('METHOD', 'SetExpressCheckout');
        $request->setField('RETURNURL', $this->returnUrl);
        $request->setField('CANCELURL', $this->cancelUrl);

        $this->addVersionField($request);
        $this->addAuthorizeFields($request);
        
        return $this->doRequest($request);
    }

    /**
     * Require: TOKEN
     * 
     * @param \Buzz\Message\Form\FormRequest $request
     * 
     * @return \Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response
     */
    public function getExpressCheckoutDetails(FormRequest $request)
    {
        $request->setField('METHOD', 'GetExpressCheckoutDetails');
        
        $this->addVersionField($request);
        $this->addAuthorizeFields($request);
        
        return $this->doRequest($request);
    }

    /**
     * Require: PAYMENTREQUEST_0_AMT, PAYMENTREQUEST_0_PAYMENTACTION, PAYERID, TOKEN
     *
     * @param \Buzz\Message\Form\FormRequest $request
     *
     * @return \Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response
     */
    public function doExpressCheckoutPayment(FormRequest $request)
    {
        $request->setField('METHOD', 'DoExpressCheckoutPayment');

        $this->addVersionField($request);
        $this->addAuthorizeFields($request);

        return $this->doRequest($request);
    }

    /**
     * @param \Buzz\Message\Form\FormRequest $request
     * 
     * @throws \Payum\Exception\Http\HttpResponseNotSuccessfulException
     * 
     * @return \Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response
     */
    protected function doRequest(FormRequest $request)
    {
        $request->setMethod('POST');
        $request->fromUrl($this->getApiEndpoint());

        $this->client->send($request, $response = $this->createResponse());
        
        if (false == $response->isSuccessful()) {
            throw new HttpResponseNotSuccessfulException('The request failed with status '.$response->getStatusCode());
        }

        return $response;
    }

    public function getAuthorizeTokenUrl($token)
    {
        $host = $this->debug ? 'www.sandbox.paypal.com' : 'www.paypal.com';

        return sprintf(
            'https://%s/cgi-bin/webscr?cmd=_express-checkout&token=%s',
            $host,
            $token
        );
    }

    protected function getApiEndpoint()
    {
        return $this->debug ?
            'https://api-3t.sandbox.paypal.com/nvp' :
            'https://api-3t.paypal.com/nvp'
            ;
    }
    
    protected function addAuthorizeFields(FormRequest $request)
    {
        $request->setField('PWD', $this->password);
        $request->setField('USER', $this->username);
        $request->setField('SIGNATURE', $this->signature);
    }
    
    protected function addVersionField(FormRequest $request)
    {
        $request->setField('VERSION', self::VERSION);
    }

    /**
     * @return \Payum\Paypal\ExpressCheckout\Nvp\Buzz\Response
     */
    protected function createResponse()
    {
        return new Response();
    }
}