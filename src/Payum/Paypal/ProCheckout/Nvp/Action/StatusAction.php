<?php
namespace Payum\Paypal\ProCheckout\Nvp\Action;

use Payum\Action\ActionInterface;
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
        
        $request->markSuccess();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof BinaryMaskStatusRequest &&
            $request->getModel() instanceof PaymentDetails
        ;
    }
}