<?php
namespace Payum\OmnipayBridge\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\PaymentAwareInterface;
use Payum\Core\PaymentInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\GetHttpRequest;

class OnsiteCaptureAction extends BaseApiAwareAction implements PaymentAwareInterface
{
    /**
     * @var PaymentInterface
     */
    protected $payment;

    /**
     * {@inheritDoc}
     */
    public function setPayment(PaymentInterface $payment)
    {
        $this->payment = $payment;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        if (!$this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if ($details['_status']) {
            return;
        }

        if (false == $details['returnUrl'] && $request->getToken()) {
            $details['returnUrl'] = $request->getToken()->getTargetUrl();
        }

        if (false == $details['cancelUrl'] && $request->getToken()) {
            $details['cancelUrl'] = $request->getToken()->getTargetUrl();
        }

        if (false == $details['clientIp']) {
            $this->payment->execute($httpRequest = new GetHttpRequest);

            $details['clientIp'] = $httpRequest->clientIp;
        }

        if (isset($details['_completeCaptureRequired'])) {
            unset($details['_completeCaptureRequired']);
            $response = $this->gateway->completePurchase($details->toUnsafeArray())->send();
        } else {
            $response = $this->gateway->purchase($details->toUnsafeArray())->send();
        }

        if ($response->isRedirect()) {
            $details['_completeCaptureRequired'] = 1;
            
            if ($response->getRedirectMethod() == 'POST') {
                throw new HttpPostRedirect($response->getRedirectUrl(), $response->getRedirectData());
            }
            else {
                throw new HttpRedirect($response->getRedirectUrl());
            }
        }

        $details['_reference']      = $response->getTransactionReference();
        $details['_status']         = $response->isSuccessful() ? 'captured' : 'failed';
        $details['_status_code']    = $response->getCode();
        $details['_status_message'] = $response->isSuccessful() ? '' : $response->getMessage();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
