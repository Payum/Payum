<?php

namespace Payum\Klarna\Invoice\Request\Api;

use KlarnaAddr;

class GetAddresses
{
    /**
     * @var string
     */
    protected $pno;

    /**
     * @var KlarnaAddr[]
     */
    protected $addresses;

    /**
     * @param string $pno
     */
    public function __construct($pno)
    {
        $this->pno = $pno;
        $this->addresses = [];
    }

    public function getPno(): string
    {
        return $this->pno;
    }

    public function addAddress(KlarnaAddr $address): void
    {
        $this->addresses[] = $address;
    }

    /**
     * @return KlarnaAddr[]
     */
    public function getAddresses(): array
    {
        return $this->addresses;
    }

    public function getFirstAddress(): ?\KlarnaAddr
    {
        return $this->addresses[0] ?? null;
    }
}
