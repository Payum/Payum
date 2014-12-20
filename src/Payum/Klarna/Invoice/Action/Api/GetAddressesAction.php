<?php
namespace Payum\Klarna\Invoice\Action\Api;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Invoice\Request\Api\GetAddresses;

class GetAddressesAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param GetAddresses $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $klarna = $this->getKlarna();

        foreach ($klarna->getAddresses($request->getPno()) as $address) {
            /** @var \KlarnaAddr $address */
            $address->setEmail(utf8_encode($address->getEmail()));
            $address->setTelno(utf8_encode($address->getTelno()));
            $address->setCellno(utf8_encode($address->getCellno()));
            $address->setFirstName(utf8_encode($address->getFirstName()));
            $address->setLastName(utf8_encode($address->getLastName()));
            $address->setCompanyName(utf8_encode($address->getCompanyName()));
            $address->setCareof(utf8_encode($address->getCareof()));
            $address->setStreet(utf8_encode($address->getStreet()));
            $address->setHouseNumber(utf8_encode($address->getHouseNumber()));
            $address->setHouseExt(utf8_encode($address->getHouseExt()));
            $address->setZipCode(utf8_encode($address->getZipCode()));
            $address->setCity(utf8_encode($address->getCity()));
            $address->setCountry(utf8_encode($address->getCountry()));

            $request->addAddress($address);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof GetAddresses;
    }
}
