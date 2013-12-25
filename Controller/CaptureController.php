<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Core\Request\BinaryMaskStatusRequest;
use Payum\Core\Request\SecuredCaptureRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CaptureController extends PayumController
{
    public function doSessionTokenAction(Request $request)
    {
        if (false == $request->getSession()) {
            throw new HttpException(400, 'This controller requires session to be started.');
        }

        if (false == $hash = $request->getSession()->get('payum_token')) {
            throw new HttpException(400, 'This controller requires token hash to be stored in the session.');
        }

        $request->getSession()->remove('payum_token');

        return $this->redirect($this->generateUrl('payum_capture_do', array_replace(
            $request->query->all(),
            array(
                'payum_token' => $hash,
            )
        )));
    }

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
}