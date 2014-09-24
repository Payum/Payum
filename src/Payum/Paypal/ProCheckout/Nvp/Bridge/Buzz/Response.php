<?php
namespace Payum\Paypal\ProCheckout\Nvp\Bridge\Buzz;

use Buzz\Message\Response as BaseResponse;

/**
 *
 */
class Response extends BaseResponse implements \ArrayAccess, \IteratorAggregate
{
    /**
     * @var array
     */
    protected $nvp;

    /**
     * @return array
     */
    public function toArray()
    {
        $this->parseNvp();

        return $this->nvp;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        $this->parseNvp();

        return isset($this->nvp[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        $this->parseNvp();

        return isset($this->nvp[$offset]) ? $this->nvp[$offset] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->parseNvp();

        $this->nvp[$offset] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        $this->parseNvp();

        unset($this->nvp[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        $this->parseNvp();

        return new \ArrayIterator($this->nvp);
    }

    /**
     * @return array
     */
    protected function parseNvp()
    {
        if (null === $this->nvp) {
            parse_str($this->getContent(), $this->nvp);
            foreach ($this->nvp as &$value) {
                $value = urldecode($value);
            }
        }
    }
}
