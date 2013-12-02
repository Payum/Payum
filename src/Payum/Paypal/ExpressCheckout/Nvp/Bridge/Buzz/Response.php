<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz;

use Buzz\Message\Response as BaseResponse;

class Response extends BaseResponse implements \ArrayAccess, \IteratorAggregate
{
    /**
     * @var array
     */
    protected $nvp;
    
    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        $this->parseNvp();
        
        return isset($this->nvp[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        $this->parseNvp();

        return isset($this->nvp[$offset]) ? $this->nvp[$offset] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->parseNvp();
        
        $this->nvp[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->parseNvp();

        unset($this->nvp[$offset]);
    }
    
    /**
     * {@inheritdoc}
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