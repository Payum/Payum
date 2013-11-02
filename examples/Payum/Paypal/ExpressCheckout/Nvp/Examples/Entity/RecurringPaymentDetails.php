<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Examples\Entity;

use Doctrine\ORM\Mapping as ORM;

use Payum\Paypal\ExpressCheckout\Nvp\Model\RecurringPaymentDetails as BaseRecurringPaymentDetails;

/**
 * @ORM\Entity
 */
class RecurringPaymentDetails extends BaseRecurringPaymentDetails
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