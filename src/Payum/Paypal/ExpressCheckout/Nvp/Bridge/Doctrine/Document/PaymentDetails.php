<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

use Payum\Paypal\ExpressCheckout\Nvp\Model\PaymentDetails as BasePaymentDetails;

class PaymentDetails extends BasePaymentDetails
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