<?php
namespace Payum\PaymentBundle\Context;

use Payum\Exception\InvalidArgumentException;

class ContextRegistry
{
    /**
     * @var array
     */
    protected $contexts = array();

    /**
     * @param ContextInterface $context
     */
    public function addContext(ContextInterface $context)
    {
        $this->contexts[$context->getName()] = $context;
    }

    /**
     * @param string $name
     * 
     * @throws \Payum\Exception\InvalidArgumentException
     * 
     * @return ContextInterface
     */
    public function getContext($name)
    {
        if (false == $this->hasContext($name)) {
            throw new InvalidArgumentException(sprintf(
                'Cannot find context for given name: %s',
                $name
            ));
        }
        
        return $this->contexts[$name];
    }

    /**
     * @param string $name
     *
     * @return boolean
     */
    public function hasContext($name)
    {
        return isset($this->contexts[$name]);
    }
}