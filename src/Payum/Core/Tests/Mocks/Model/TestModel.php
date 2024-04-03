<?php

namespace Payum\Core\Tests\Mocks\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class TestModel
{
    public mixed $payum_id = null;

    protected $id;

    protected $price;

    protected $currency;

    public function getId()
    {
        return $this->id;
    }

    public function setPrice($price): void
    {
        $this->price = $price;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setCurrency($currency): void
    {
        $this->currency = $currency;
    }

    public function getCurrency()
    {
        return $this->currency;
    }
}
