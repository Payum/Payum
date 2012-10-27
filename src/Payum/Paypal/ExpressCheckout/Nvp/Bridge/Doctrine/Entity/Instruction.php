<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

use Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction as BaseInstruction;

class Instruction extends BaseInstruction
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}