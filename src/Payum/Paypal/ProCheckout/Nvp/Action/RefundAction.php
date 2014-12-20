<?php
namespace Payum\Paypal\ProCheckout\Nvp\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Paypal\ProCheckout\Nvp\Api;
use Payum\Core\Request\Refund;

class RefundAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Refund */
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (null === $details['RESULT']) {
            return;
        }

        $refundableTrxTypes = array(Api::TRXTYPE_SALE, Api::TRXTYPE_DELAYED_CAPUTER, Api::TRXTYPE_VOICE_AUTHORIZATION);
        if (false == in_array($details['TRXTYPE'], $refundableTrxTypes)) {
            throw new LogicException(sprintf(
                'You cannot refund transaction with type %s. Only these types could be refunded: %s',
                $details['TRXTYPE'],
                implode(', ', $refundableTrxTypes)
            ));
        }

        $details->validateNotEmpty(array('PNREF'), true);

        $details['PURCHASE_TRXTYPE'] = $details['TRXTYPE'];
        $details['TRXTYPE'] = null;

        $details['PURCHASE_RESULT'] = $details['RESULT'];
        $details['RESULT'] = null;

        $details['ORIGID'] = $details['PNREF'];

        $details->replace($this->api->doCredit($details->toUnsafeArray()));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Refund &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
