<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use ArrayAccess;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoCapture;

class CaptureAction extends PurchaseAction
{
    public function execute(mixed $request): void
    {
        /** @var Capture $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $details['PAYMENTREQUEST_0_PAYMENTACTION'] = Api::PAYMENTACTION_SALE;

        foreach (range(0, 9) as $index) {
            if (Api::PENDINGREASON_AUTHORIZATION == $details['PAYMENTINFO_' . $index . '_PENDINGREASON']) {
                $details->defaults([
                    'PAYMENTREQUEST_' . $index . '_COMPLETETYPE' => 'Complete',
                ]);

                $this->gateway->execute(new DoCapture($details, $index));
            }
        }

        parent::execute($request);
    }

    public function supports(mixed $request): bool
    {
        return $request instanceof Capture &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
