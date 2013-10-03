<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Examples\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;
use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Document\RecurringPaymentDetails as BaseRecurringPaymentDetails;

/**
 * @Mongo\Document
 */
class RecurringPaymentDetails extends BaseRecurringPaymentDetails
{
    /**
     * @Mongo\Id
     */
    protected $id;
}