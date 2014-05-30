<?php
namespace Payum\AuthorizeNet\Aim\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Request\UserInputRequiredInteractiveRequest;
use Payum\Core\Exception\RequestNotSupportedException;

class CaptureAction implements ActionInterface, ApiAwareInterface
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
        
        if (false == ($model['amount'] && $model['card_num'] && $model['exp_date'])) {
            throw new UserInputRequiredInteractiveRequest(array('amount', 'card_num', 'exp_date'));
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