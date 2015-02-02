<?php
namespace Payum\Core\Tests\Mocks\Model;

use Payum\Core\Exception\LogicException;

class PropelModel
{
    protected $id;

    protected $price;

    protected $currency;

    public function getId()
    {
        return $this->id;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function findPk($id)
    {
        $this->id = $id;

        return $this;
    }

    public function save()
    {
        throw new LogicException('Save method was triggered.');
    }
}
