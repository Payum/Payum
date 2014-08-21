<?php
namespace Payum\Klarna\Invoice\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
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
        $details->validateNotEmpty(array('rno'));

        $klarna = $this->createKlarna();

        try {
            $details['status'] = $klarna->checkOrderStatus($details['rno']);
        } catch (\KlarnaException $e) {
            $details['error_request'] = get_class($request);
            $details['error_file'] = $e->getFile();
            $details['error_line'] = $e->getLine();
            $details['error_code'] = $e->getCode();
            $details['error_message'] = $e->getMessage();
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