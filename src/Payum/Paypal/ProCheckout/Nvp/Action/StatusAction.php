<?php
namespace Payum\Paypal\ProCheckout\Nvp\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Paypal\ProCheckout\Nvp\Api;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Exception\RequestNotSupportedException;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     * 
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = new ArrayObject($request->getModel());
        
        if (null === $model['RESULT']) {
            $request->markNew();
            
            return;
        }

        if (Api::RESULT_SUCCESS === (int) $model['RESULT']) {
            $request->markCaptured();

            return;
        }

        $request->markFailed();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
