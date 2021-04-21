<?php
namespace Payum\Core\Tests\Mocks\Model;

use Payum\Core\Exception\LogicException;

class Propel2Model
{
    protected $id;

    protected $price;

    protected $currency;

    public function setId($id)
    {
        $this->id = $id;
    }

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

    public function save()
    {
        throw new LogicException('Save method was triggered.');
    }
}
