<?php

namespace Payum\Klarna\Invoice\Action\Api;

use KlarnaAddr;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Invoice\Request\Api\GetAddresses;

class GetAddressesAction extends BaseApiAwareAction
{
    /**
     * @param GetAddresses $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $klarna = $this->getKlarna();

        foreach ($klarna->getAddresses($request->getPno()) as $address) {
            /** @var KlarnaAddr $address */
            $address->setEmail(mb_convert_encoding((string) $address->getEmail(), 'UTF-8', 'ISO-8859-1'));
            $address->setTelno(mb_convert_encoding((string) $address->getTelno(), 'UTF-8', 'ISO-8859-1'));
            $address->setCellno(mb_convert_encoding((string) $address->getCellno(), 'UTF-8', 'ISO-8859-1'));
            $address->setFirstName(mb_convert_encoding((string) $address->getFirstName(), 'UTF-8', 'ISO-8859-1'));
            $address->setLastName(mb_convert_encoding((string) $address->getLastName(), 'UTF-8', 'ISO-8859-1'));
            $address->setCompanyName(mb_convert_encoding((string) $address->getCompanyName(), 'UTF-8', 'ISO-8859-1'));
            $address->setCareof(mb_convert_encoding((string) $address->getCareof(), 'UTF-8', 'ISO-8859-1'));
            $address->setStreet(mb_convert_encoding((string) $address->getStreet(), 'UTF-8', 'ISO-8859-1'));
            $address->setHouseNumber(mb_convert_encoding((string) $address->getHouseNumber(), 'UTF-8', 'ISO-8859-1'));
            $address->setHouseExt(mb_convert_encoding((string) $address->getHouseExt(), 'UTF-8', 'ISO-8859-1'));
            $address->setZipCode(mb_convert_encoding((string) $address->getZipCode(), 'UTF-8', 'ISO-8859-1'));
            $address->setCity(mb_convert_encoding((string) $address->getCity(), 'UTF-8', 'ISO-8859-1'));
            $address->setCountry(mb_convert_encoding((string) $address->getCountry(), 'UTF-8', 'ISO-8859-1'));

            $request->addAddress($address);
        }
    }

    public function supports($request)
    {
        return $request instanceof GetAddresses;
    }
}
