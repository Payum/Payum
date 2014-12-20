<?php
namespace Payum\Klarna\Invoice\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\PaymentAwareInterface;
use Payum\Core\PaymentInterface;
use Payum\Klarna\Invoice\Request\Api\CreditPart;
use Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails;

class CreditPartAction extends BaseApiAwareAction implements PaymentAwareInterface
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
     * @param CreditPart $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $details->validateNotEmpty(array('invoice_number'));

        $klarna = $this->getKlarna();

        try {
            $this->payment->execute(new PopulateKlarnaFromDetails($details, $klarna));

            $details['refund_invoice_number'] = $klarna->creditPart($details['invoice_number']);
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
            $request instanceof CreditPart &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
