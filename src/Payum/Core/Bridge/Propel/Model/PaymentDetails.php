<?php

namespace Payum\Core\Bridge\Propel\Model;

use Payum\Core\Bridge\Propel\Model\PaymentDetailsPeer;
use Payum\Core\Bridge\Propel\Model\om\BasePaymentDetails;

class PaymentDetails extends BasePaymentDetails implements \ArrayAccess, \IteratorAggregate
{
    protected $array = array();

    /**
     * {@inheritDoc}
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false)
    {
        $result = parent::hydrate($row, $startcol, $rehydrate);

        if (!empty($this->details)) {
            $this->array = unserialize($this->details);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function save(PropelPDO $con = NULL)
    {
        if (!empty($this->array)) {
            $this->setDetails(serialize($this->array));
        }

        return parent::save();
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->array);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        return $this->array[$offset];
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->array[$offset] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->array);
    }
}
