<?php
namespace Payum\Payex\Examples\Entity;

use Doctrine\ORM\Mapping as ORM;

use Payum\Payex\Bridge\Doctrine\Entity\PaymentDetails as BasePaymentDetails;

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