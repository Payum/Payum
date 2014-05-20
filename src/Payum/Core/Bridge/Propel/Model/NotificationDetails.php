<?php

namespace Payum\Core\Bridge\Propel\Model;

use Payum\Core\Bridge\Propel\Model\om\BaseNotificationDetails;

class NotificationDetails extends BaseNotificationDetails implements \ArrayAccess, \IteratorAggregate
{
    protected $array = array();

    /**
     * {@inheritDoc}
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false)
    {
        $result = parent::hydrate($row, $startcol, $rehydrate);

        if (!empty($this->details)) {
            $this->details = unserialize($this->details);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function setDetails($details)
    {
        if (is_array($details) && !empty($details)) {
            $details = serialize($details);
        }

        return parent::setDetails($details);
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
