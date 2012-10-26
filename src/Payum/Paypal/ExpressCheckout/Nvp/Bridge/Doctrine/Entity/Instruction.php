<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

use Payum\Paypal\ExpressCheckout\Nvp\Instruction as BaseInstruction;

/**
 * @ORM\Table(name="payum_paypal_express_checkout_nvp_instructions")
 * @ORM\Entity
 */
abstract class Instruction extends BaseInstruction
{
}