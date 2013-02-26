<?php
namespace Payum\OmnipayBridge\Action;

use Omnipay\Common\RedirectResponse;

use Omnipay\Common\Request;
use Payum\Exception\LogicException;
use Payum\Exception\RequestNotSupportedException;
use Payum\Request\CaptureRequest;
use Payum\Request\RedirectUrlInteractiveRequest;

class CaptureAction extends BaseActionApiAware
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        try {
            $options = $request->getModel();
            
            $response = $this->gateway->purchase((array) $options);

            $options['_reference'] = $response->getGatewayReference();
            $options['_status_message'] = '';
            
            if ($response instanceof RedirectResponse) {                
                throw new RedirectUrlInteractiveRequest($response->getRedirectUrl());
            }
            
            if ($response->isSuccessful()) {
                $options['_status'] = 'success';
            } else {
                $options['_status'] = 'failed';
                $options['_status_message'] = $response->getMessage();
            }
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
            (
                $request->getModel() instanceof \ArrayObject ||
                $request->getModel() instanceof Request
            )
        ;
    }
}