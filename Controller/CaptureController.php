<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Payum\Exception\InvalidArgumentException;
use Payum\Exception\RequestNotSupportedException;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Bundle\PayumBundle\Request\CaptureTokenizedDetailsRequest;
use Payum\Model\TokenizedDetails;
use Payum\Bundle\PayumBundle\Service\TokenManager;
use Payum\Bundle\PayumBundle\Registry\ContainerAwareRegistry;

class CaptureController extends Controller 
{
    public function doAction($paymentName, $token, Request $request)
    {
        try {
            if (false == $token instanceof TokenizedDetails) {
                if (false == $token = $this->getTokenManager()->findByToken($paymentName, $token)) {
                    throw $this->createNotFoundException('The TokenizedDetails with requested token not found.');
                }
            }
            if ($paymentName !== $token->getPaymentName()) {
                throw new InvalidArgumentException(sprintf('The paymentName %s not match one %s set in the token.', $paymentName, $token->getPaymentName()));
            }
            //TODO not working with forward.
//            if (parse_url($request->getUri(), PHP_URL_PATH) != parse_url($token->getTargetUrl(), PHP_URL_PATH)) {
//                throw new InvalidArgumentException(sprintf('The current url %s not match target url %s set in the token.', $request->getRequestUri(), $token->getTargetUrl()));
//            }
            
            $status = new BinaryMaskStatusRequest($token);
            $this->getPayum()->getPayment($paymentName)->execute($status);
            if (false == $status->isNew()) {
                throw new HttpException(400, 'The model status must be new.');
            }
            
            $capture = new CaptureTokenizedDetailsRequest($token);
            $this->getPayum()->getPayment($paymentName)->execute($capture);
            
            $this->getPayum()->getStorageForClass($token, $paymentName)->deleteModel($token);
            
            return $this->redirect($token->getAfterUrl());
        } catch (HttpException $e) {
            throw $e;
        } catch (InvalidArgumentException $e) {
            throw new HttpException(404, 'The input parameters not valid.', $e);
        }
    }

    /**
     * @return ContainerAwareRegistry
     */
    protected function getPayum()
    {
        return $this->get('payum');
    }

    /**
     * @return TokenManager
     */
    protected function getTokenManager()
    {
        return $this->get('payum.token_manager');
    }
}