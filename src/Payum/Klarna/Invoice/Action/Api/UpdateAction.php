<?php
namespace Payum\Klarna\Invoice\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails;
use Payum\Klarna\Invoice\Request\Api\Update;

class UpdateAction extends BaseApiAwareAction implements GatewayAwareInterface
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
     * @param Update $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $klarna = $this->getKlarna();

        try {
            $this->gateway->execute(new PopulateKlarnaFromDetails($details, $klarna));

            $details['updated'] = $klarna->update($details['rno']);
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
            $request instanceof Update &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
