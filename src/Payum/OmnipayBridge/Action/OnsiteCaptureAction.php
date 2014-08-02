<?php
namespace Payum\OmnipayBridge\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;

class OnsiteCaptureAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        if (!$this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $options = ArrayObject::ensureArrayObject($request->getModel());

        if ($options['_status']) {
            return;
        }

        if (isset($options['_completeCaptureRequired'])) {
            unset($options['_completeCaptureRequired']);
            $response = $this->gateway->completePurchase($options->toUnsafeArray())->send();
        } else {
            $response = $this->gateway->purchase($options->toUnsafeArray())->send();
        }

        if ($response->isRedirect()) {
            $options['_completeCaptureRequired'] = 1;
            
            if ($response->getRedirectMethod() == 'POST') {
                throw new HttpPostRedirect($response->getRedirectUrl(), $response->getRedirectData());
            }
            else {
                throw new HttpRedirect($response->getRedirectUrl());
            }
        }

        $options['_reference']      = $response->getTransactionReference();
        $options['_status']         = $response->isSuccessful() ? 'success' : 'failed';
        $options['_status_code']    = $response->getCode();
        $options['_status_message'] = $response->isSuccessful() ? '' : $response->getMessage();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
