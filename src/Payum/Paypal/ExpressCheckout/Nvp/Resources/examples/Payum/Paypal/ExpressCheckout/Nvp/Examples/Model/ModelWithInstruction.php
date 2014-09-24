<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Examples\Model;

use Payum\Core\Model\DetailsAggregateInterface;
use Payum\Core\Model\DetailsAwareInterface;

class ModelWithInstruction implements \Payum\Core\Model\DetailsAggregateInterface, \Payum\Core\Model\DetailsAwareInterface
{
    /**
     * @var object
     */
    protected $details;

    /**
     * {@inheritDoc}
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * {@inheritDoc}
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }
}