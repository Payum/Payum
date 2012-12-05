<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Examples\Entity;

use Doctrine\ORM\Mapping as ORM;

use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Entity\PaymentInstruction as BasePaymentInstruction;

/**
 * @ORM\Entity
 */
class PaymentInstruction extends BasePaymentInstruction
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer $id
     */
    protected $id; 
}