<?php
namespace Payum\Be2Bill\Action;

use Payum\Action\ActionInterface;
use Payum\ApiAwareInterface;
use Payum\Bridge\Spl\ArrayObject;
use Payum\Request\CaptureRequest;
use Payum\Request\UserInputRequiredInteractiveRequest;
use Payum\Exception\UnsupportedApiException;
use Payum\Exception\RequestNotSupportedException;
use Payum\Be2Bill\Api;

class CaptureAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @var Api
     */
    protected $api;
    
    /**
     * {@inheritdoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof Api) {
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

        $model = new ArrayObject($request->getModel());
        
        if (null !== $model['EXECCODE']) {
            return;
        }

        $requiredCardFields = array('CARDCODE', 'CARDCVV', 'CARDVALIDITYDATE', 'CARDFULLNAME');
        
        //instruction must have an alias set (e.g oneclick payment) or credit card info. 
        if (false == ($model['ALIAS'] || $model->validatedNotEmpty($requiredCardFields, false))) {
            throw new UserInputRequiredInteractiveRequest($requiredCardFields);
        }

        $response = $this->api->payment((array) $model);

        $model->replace((array) $response->getContentJson());
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