<?php
namespace Payum\Core\Tests\Mocks\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class TestModel
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
}
