<?php
namespace Payum\Klarna\Invoice\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\PaymentAwareInterface;
use Payum\Core\PaymentInterface;
use Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails;
use Payum\Klarna\Invoice\Request\Api\ReserveAmount;

class ReserveAmountAction extends BaseApiAwareAction implements PaymentAwareInterface
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
     * @param ReserveAmount $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $klarna = $this->getKlarna();

        $this->payment->execute(new PopulateKlarnaFromDetails($details, $klarna));

        try {
            $result = $klarna->reserveAmount(
                $details['pno'],
                $details['gender'],
                $details['amount'] ?: -1,
                $details['reservation_flags'] ?: \KlarnaFlags::NO_FLAG
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
            $request instanceof ReserveAmount &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}