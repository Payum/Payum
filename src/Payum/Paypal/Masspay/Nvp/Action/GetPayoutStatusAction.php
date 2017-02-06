<?php
namespace Payum\Paypal\Masspay\Nvp\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Payum\Paypal\Masspay\Nvp\Api;

class GetPayoutStatusAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        
        if (false == $model['ACK']) {
            $request->markNew();

            return;
        }

        if (in_array($model['ACK'], [Api::ACK_SUCCESS, Api::ACK_SUCCESS_WITH_WARNING])) {
            $request->markPayedout();

            return;
        }

        if (in_array($model['ACK'], [Api::ACK_FAILURE, Api::ACK_FAILURE_WITH_WARNING])) {
            $request->markFailed();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
