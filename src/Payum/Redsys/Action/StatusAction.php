<?php
namespace Payum\Redsys\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     */
    public function execute( $request )
    {
        /** @var $request GetStatusInterface */
        RequestNotSupportedException::assertSupports( $this, $request );

        $model = ArrayObject::ensureArrayObject( $request->getModel() );

        if (false == $model['Ds_Response']) {
            $request->markNew();

            return;
        }

        // cast to int 'Ds_Response' for make the checks easier
        $dsResponse = (int)$model['Ds_Response'];

        // bank will provide you the doc with the error codes
        // we only check if the bank has give us a positive response.
        // For that, the casted to int value must be between 0 and 99
        // Only in that case we mark the request as captured
        if (0 <= $dsResponse && 99 >= $dsResponse) {
            $request->markCaptured();
            return;
        }

        // in any other case we mark the request as failed
        // and set the error code in the request too 
        // so developers can decide what to do with that code
        // (maybe building their own messages errors)
        $request->markFailed();
    }

    /**
     * {@inheritDoc}
     */
    public function supports( $request )
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
