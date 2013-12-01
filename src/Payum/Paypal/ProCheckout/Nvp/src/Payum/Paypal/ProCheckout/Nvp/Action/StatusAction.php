<?php
namespace Payum\Paypal\ProCheckout\Nvp\Action;

use Payum\Action\ActionInterface;
use Payum\Bridge\Spl\ArrayObject;
use Payum\Paypal\ProCheckout\Nvp\Api;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Exception\RequestNotSupportedException;
use Payum\Paypal\ProCheckout\Nvp\Model\PaymentDetails;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class StatusAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request \Payum\Request\StatusRequestInterface */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = new ArrayObject($request->getModel());
        
        if (null === $model['RESULT']) {
            $request->markNew();
            
            return;
        }

        if (Api::RESULT_SUCCESS === (int) $model['RESULT']) {
            $request->markSuccess();

            return;
        }

        $request->markFailed();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof BinaryMaskStatusRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}