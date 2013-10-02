<?php
/**
 * Created by PhpStorm.
 * User: skadabr
 * Date: 9/25/13
 * Time: 4:27 PM
 */
namespace Payum\Paypal\Rest\Action;

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Rest\ApiContext;
use Payum\Action\PaymentAwareAction;
use Payum\ApiAwareInterface;
use Payum\Exception\UnsupportedApiException;
use Payum\Request\CaptureRequest;
use Payum\Request\RedirectUrlInteractiveRequest;

class CaptureAction extends PaymentAwareAction implements ApiAwareInterface
{
    /** @param ApiContext */
    protected $api;
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }



        /** @var $payment Payment */

        if ($_GET['PayerID'] == null) {
            $payment = $request->getModel();
            $payment->create($this->api);

            foreach($payment->getLinks() as $link) {
                if($link->getRel() == 'approval_url') {
                    throw new RedirectUrlInteractiveRequest($link->getHref());

                }
            }
        } else {

            // ### Api Context
            // Pass in a `ApiContext` object to authenticate
            // the call and to send a unique request id
            // (that ensures idempotency). The SDK generates
            // a request id if you do not pass one explicitly.
            $apiContext = $this->api;

            $paymentId = $payment->getId();
            $payment = Payment::get($paymentId);
            // PaymentExecution object includes information necessary
            // to execute a PayPal account payment.
            // The payer_id is added to the request query parameters
            // when the user is redirected from paypal back to your site
            $execution = new PaymentExecution();
            $execution->setPayer_id($_GET['PayerID']);

            //Execute the payment
            $payment->execute($execution, $apiContext);
        }




        //todo check create model
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof Payment
            ;
    }

    /**
     * @param mixed $api
     *
     * @throws UnsupportedApiException if the given Api is not supported.
     *
     * @return void
     */
    public function setApi($api)
    {
        if(false == $api instanceof ApiContext) {
            throw new UnsupportedApiException('asdfasdfasdfasd');
        }

        $this->api = $api;
    }
}
