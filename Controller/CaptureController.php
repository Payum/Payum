<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\SecuredCaptureRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CaptureController extends PayumController
{
    public function doAction(Request $request)
    {
        $token = $this->getHttpRequestVerifier()->verify($request);

        $payment = $this->getPayum()->getPayment($token->getPaymentName());
        
        $status = new BinaryMaskStatusRequest($token);
        $payment->execute($status);
        if (false == $status->isNew()) {
            throw new HttpException(400, 'The model status must be new.');
        }
        
        $payment->execute(new SecuredCaptureRequest($token));
        
        $this->getHttpRequestVerifier()->invalidate($token);
        
        return $this->redirect($token->getAfterUrl());
    }

    /**
     * @deprecated since 0.6 will be removed in 0.7. This route present for easy migration from 0.5 version.
     */
    public function doDeprecatedAction(Request $request)
    {
        return $this->forward('Payum:Capture:do', array(
            'payum_token' => $request->attributes->get('token', $request->get('token'))
        ));
    }
}