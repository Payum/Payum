<?php
namespace Payum\Klarna\Invoice\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Klarna\Invoice\Request\Api\ActivateReservation;
use Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails;

class ActivateReservationAction extends BaseApiAwareAction implements GatewayAwareInterface
{
    /**
     * @var GatewayInterface
     */
    protected $gateway;

    /**
     * {@inheritDoc}
     */
    public function setGateway(GatewayInterface $gateway)
    {
        $this->gateway = $gateway;
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

        $klarna = $this->getKlarna();

        try {
            $this->gateway->execute(new PopulateKlarnaFromDetails($details, $klarna));

            $result = $klarna->activateReservation(
                $details['pno'],
                $details['rno'],
                $details['gender'],
                $details['ocr'],
                $details['activate_reservation_flags'] ?: \KlarnaFlags::NO_FLAG
            );

            $details['risk_status'] = $result[0];
            $details['invoice_number'] = $result[1];
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
