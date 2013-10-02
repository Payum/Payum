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

        if (false == $request->getModel()->getState()) {
            $payment = $request->getModel();
            $payment->create($this->api);

            foreach($payment->getLinks() as $link) {
                if($link->getRel() == 'approval_url') {
                    throw new RedirectUrlInteractiveRequest($link->getHref());

                }
            }
        } else {

            $paymentId = $request->getModel()->getId();
            $payment = Payment::get($paymentId);


            $execution = new PaymentExecution();
            $execution->setPayer_id($_GET['PayerID']);

            //Execute the payment
            $payment->execute($execution, $this->api);
            $request->getModel()->fromArray($payment->toArray());

           // $this->sincModel($request, $payment);
        }
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

    protected function sincModel($request, $payment)
    {
        $request->getModel()->setState($payment->getState());
        $request->getId()->setId($payment->getId());
        $request->getLinks()->setLinks($payment->getLinks());
    }
}
