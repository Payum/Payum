<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Examples\Model;

use Payum\Model\DetailsAggregateInterface;
use Payum\Model\DetailsAwareInterface;

class ModelWithInstruction implements DetailsAggregateInterface, DetailsAwareInterface
{
    /**
     * @var object
     */
    protected $details;

    /**
     * {@inheritdoc}
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * {@inheritdoc}
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }
}