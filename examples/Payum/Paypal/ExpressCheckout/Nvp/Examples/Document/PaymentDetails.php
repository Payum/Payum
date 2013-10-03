<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Examples\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;
use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Document\PaymentDetails as BasePaymentDetails;

/**
 * @Mongo\Document
 */
class PaymentDetails extends BasePaymentDetails
{
    /**
     * @Mongo\Id
     */
    protected $id; 
}