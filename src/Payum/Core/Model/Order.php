<?php
namespace Payum\Core\Model;

class Order implements OrderInterface
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var string
     */
    protected $number;

    /**
     * @var int
     */
    protected $totalAmount;

    /**
     * @var string
     */
    protected $totalCurrency;

    /**
     * @var array
     */
    protected $details;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return int
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * @param int $totalAmount
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;
    }

    /**
     * @return string
     */
    public function getTotalCurrency()
    {
        return $this->totalCurrency;
    }

    /**
     * @param string $totalCurrency
     */
    public function setTotalCurrency($totalCurrency)
    {
        $this->totalCurrency = $totalCurrency;
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param array $details
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }
} 