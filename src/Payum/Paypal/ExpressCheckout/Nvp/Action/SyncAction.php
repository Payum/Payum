<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Bridge\Spl\ArrayObject;
use Payum\Request\SyncRequest;
use Payum\Action\ActionPaymentAware;
use Payum\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Exception\Http\HttpResponseAckNotSuccessException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\GetExpressCheckoutDetailsRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\GetTransactionDetailsRequest;

class SyncAction extends ActionPaymentAware
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request SyncRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        $model = new ArrayObject($request->getModel());
        
        if (false == $model['TOKEN']) {
            return;
        }
        
        try {
            $this->payment->execute(new GetExpressCheckoutDetailsRequest($model));
            
            foreach (range(0, 9) as $index) {
                if ($model['PAYMENTREQUEST_'.$index.'_TRANSACTIONID']) {
                    $this->payment->execute(new GetTransactionDetailsRequest($index, $model));
                }
            }
        } catch (HttpResponseAckNotSuccessException $e) {
            $model->replace($e->getResponse());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof SyncRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}