<?php
namespace Payum\Klarna\Invoice\Request\Api;

class GetAddresses
{
    /**
     * @var string
     */
    protected $pno;

    /**
     * @var \KlarnaAddr[]
     */
    protected $addresses;

    /**
     * @param string $pno
     */
    public function __construct($pno)
    {
        $this->pno = $pno;
        $this->addresses = array();
    }

    /**
     * @return string
     */
    public function getPno()
    {
        return $this->pno;
    }

    /**
     * @param \KlarnaAddr $address
     */
    public function addAddress(\KlarnaAddr $address)
    {
        $this->addresses[] = $address;
    }

    /**
     * @return \KlarnaAddr[]
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @return \KlarnaAddr|null
     */
    public function getFirstAddress()
    {
        return isset($this->addresses[0]) ? $this->addresses[0] : null;
    }
}
