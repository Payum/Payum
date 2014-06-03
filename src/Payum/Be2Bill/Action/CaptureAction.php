<?php
namespace Payum\Be2Bill\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Request\ObtainCreditCardRequest;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Be2Bill\Api;
use Payum\Core\Security\SensitiveValue;

class CaptureAction extends PaymentAwareAction implements ApiAwareInterface
{
    /**
     * @var Api
     */
    protected $api;
    
    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }
        
        $this->api = $api;
    }
    
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request \Payum\Core\Request\CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = new ArrayObject($request->getModel());
        
        if (null !== $model['EXECCODE']) {
            return;
        }

        $cardFields = array('CARDCODE', 'CARDCVV', 'CARDVALIDITYDATE', 'CARDFULLNAME');
        if (false == $model->validateNotEmpty($cardFields, false)) {
            try {
                $creditCardRequest = new ObtainCreditCardRequest;
                $this->payment->execute($creditCardRequest);
                $card = $creditCardRequest->obtain();

                $model['CARDVALIDITYDATE'] = new SensitiveValue($card->getExpireAt()->format('m-y'));
                $model['CARDCODE'] = $card->getNumber();
                $model['CARDFULLNAME'] = $card->getHolder();
                $model['CARDCVV'] = $card->getSecurityCode();
            } catch (RequestNotSupportedException $e) {
                throw new LogicException('Credit card details has to be set explicitly or there has to be an action that supports ObtainCreditCardRequest request.');
            }
        }
        
        //instruction must have an alias set (e.g oneclick payment) or credit card info. 
        if (false == ($model['ALIAS'] || $model->validateNotEmpty($cardFields, false))) {
            throw new LogicException('Either credit card fields or its alias has to be set.');
        }

        $response = $this->api->payment($model->toUnsafeArray());

        $model->replace((array) $response->getContentJson());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}