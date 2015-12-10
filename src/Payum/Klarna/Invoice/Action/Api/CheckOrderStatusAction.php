<?php
namespace Payum\Klarna\Invoice\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Invoice\Request\Api\CheckOrderStatus;

class CheckOrderStatusAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param CheckOrderStatus $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if ($details['invoice_number']) {
            return;
        }

        $details->validateNotEmpty(array('rno'));

        $klarna = $this->getKlarna();

        try {
            $details['status'] = $klarna->checkOrderStatus($details['rno']);
        } catch (\KlarnaException $e) {
            $this->populateDetailsWithError($details, $e, $request);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CheckOrderStatus &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
