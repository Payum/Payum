<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

use Payum\Paypal\ExpressCheckout\Nvp\Model\RecurringPaymentDetails as BaseRecurringPaymentDetails;

class RecurringPaymentDetails extends BaseRecurringPaymentDetails
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