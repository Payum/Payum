<?php
namespace Payum\Be2Bill\Examples\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Be2Bill\Model\PaymentDetails as BasePaymentDetails;

/**
 * @ORM\Entity
 */
class PaymentDetails extends BasePaymentDetails
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer $id
     */
    protected $id;

    public function getId()
    {
        return $this->id;
    }
}