<?php
namespace Payum\Core\Model;

class Order implements OrderInterface
{
    /**
     * @var string
     */
    protected $number;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var MoneyInterface
     */
    protected $totalPrice;

    /**
     * @var array
     */
    protected $details;

    public function __construct()
    {
        $this->number = '';
        $this->totalPrice = new Money(0);
        $this->details = array();
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
     * @return MoneyInterface
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * @param MoneyInterface $price
     */
    public function setTotalPrice(MoneyInterface $price)
    {
        $this->totalPrice = $price;
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param array|\Traversable $details
     */
    public function setDetails($details)
    {
        if ($details instanceof \Traversable) {
            $details = iterator_to_array($details);
        }

        $this->details = $details;
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }
}
