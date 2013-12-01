<?php

namespace Payum\Bridge\Omnipay\Action;

use Omnipay\Common\Message\RedirectResponseInterface;
use Payum\Bridge\Spl\ArrayObject;
use Payum\Exception\LogicException;
use Payum\Exception\RequestNotSupportedException;
use Payum\Request\CaptureRequest;
use Payum\Request\InteractiveRequestInterface;
use Payum\Request\RedirectUrlInteractiveRequest;

class CaptureAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        if (!$this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        try {
            $options = ArrayObject::ensureArrayObject($request->getModel());

            if (isset($options['_completeCaptureRequired'])) {
                unset($options['_completeCaptureRequired']);
                $response = $this->gateway->completePurchase($options->toUnsafeArray())->send();
            } else {
                $response = $this->gateway->purchase($options->toUnsafeArray())->send();
            }

            if ($response->isRedirect()) {
                throw new RedirectUrlInteractiveRequest($response->getRedirectUrl());
            }

            $options['_reference']      = $response->getTransactionReference();
            $options['_status']         = $response->isSuccessful() ? 'success' : 'failed';
            $options['_status_code']    = $response->getCode();
            $options['_status_message'] = $response->isSuccessful() ? '' : $response->getMessage();
        } catch (InteractiveRequestInterface $e) {
            $options['_completeCaptureRequired'] = 1;

            throw $e;
        } catch (\Exception $e) {
            $options['_status'] = 'failed';

            throw new LogicException('Omnipay unexpected exception', null, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
