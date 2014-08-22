<?php
namespace Payum\Klarna\Invoice\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\PaymentAwareInterface;
use Payum\Core\PaymentInterface;
use Payum\Klarna\Invoice\Request\Api\ActivateReservation;
use Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails;

class ActivateReservationAction extends BaseApiAwareAction implements PaymentAwareInterface
{
    /**
     * @var PaymentInterface
     */
    protected $payment;

    /**
     * {@inheritDoc}
     */
    public function setPayment(PaymentInterface $payment)
    {
        $this->payment = $payment;
    }

    /**
     * {@inheritDoc}
     *
     * @param ActivateReservation $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $klarna = $this->createKlarna();

        $this->payment->execute(new PopulateKlarnaFromDetails($details, $klarna));

        try {
            $result = $klarna->activateReservation(
                $details['pno'],
                $details['rno'],
                $details['gender'],
                $details['ocr'],
                $details['activate_reservation_flags'] ?: \KlarnaFlags::NO_FLAG
            );

            $details['rno'] = $result[0];
            $details['status'] = $result[1];
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
            $request instanceof ActivateReservation &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
} 