<?php
namespace Payum\Bridge\Spl;

use Payum\Exception\InvalidArgumentException;

class ArrayObject extends \ArrayObject
{
    /**
     * @param array|\Traversable $input
     * 
     * @throws \Payum\Exception\InvalidArgumentException
     * 
     * @return void
     */
    public function replace($input)
    {
        if (false == (is_array($input) instanceof \Traversable)) {
            throw new InvalidArgumentException('Invalid input given. Should be an array or instance of \Traversable');
        }
        
        foreach ($input as $index => $value) {
            $this[$index] = $value;
        }
    }
    
    /**
     * Checks that all given keys a present and contains not empty value
     * 
     * @param array $indexes
     *  
     * @return boolean
     */
    public function offsetsExists(array $indexes)
    {
        foreach ($indexes as $index) {
            if (false == $this[$index]) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * This simply returns NULL when an array does not have this index.
     * It allows us not to do isset all the time we want to access something.
     * 
     * {@inheritdoc}
     */
    public function offsetGet($index)
    {
        if ($this->offsetExists($index)) {
            return parent::offsetGet($index);
        }
        
        return null;
    }
}