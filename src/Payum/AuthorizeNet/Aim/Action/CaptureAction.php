<?php
namespace Payum\AuthorizeNet\Aim\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Request\ObtainCreditCardRequest;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Security\SensitiveValue;

class CaptureAction extends PaymentAwareAction implements ApiAwareInterface
{
    /**
     * @var AuthorizeNetAIM
     */
    protected $api;
    
    /**
     * {@inheritdoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof AuthorizeNetAIM) {
            throw new UnsupportedApiException('Not supported.');
        }
        
        $this->api = $api;
    }
    
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (null != $model['response_code']) {
            return;
        }
        
        if (false == $model->validateNotEmpty(array('card_num', 'exp_date'), false)) {
            try {
                $creditCardRequest = new ObtainCreditCardRequest;
                $this->payment->execute($creditCardRequest);
                $card = $creditCardRequest->obtain();

                $model['exp_date'] = new SensitiveValue($card->getExpireAt()->format('y/d'));
                $model['card_num'] = $card->getNumber();
            } catch (RequestNotSupportedException $e) {
                throw new LogicException('Credit card details has to be set explicitly or there has to be an action that supports ObtainCreditCardRequest request.');
            }
        }

        $api = clone $this->api;
        $api->ignore_not_x_fields = true;
        $api->setFields(array_filter($model->toUnsafeArray()));

        $response = $api->authorizeAndCapture();

        $model->replace(get_object_vars($response));
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