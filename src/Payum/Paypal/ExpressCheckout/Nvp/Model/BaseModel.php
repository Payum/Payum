<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Model;

use Payum\Exception\InvalidArgumentException;
use Payum\Exception\LogicException;

abstract class BaseModel implements \ArrayAccess, \IteratorAggregate
{
    /**
     * @return array 
     */
    abstract protected function getSupportedToNvpProperties();
    
    /**
     * @deprecated since 0.3 move the logic to offsetSet
     *
     * @param $nvp array|\Traversable
     */
    protected function fromNvp($nvp)
    {
        if (false == (is_array($nvp) || $nvp instanceof \Traversable)) {
            throw new InvalidArgumentException('Invalid nvp argument. Should be an array of an object implemented \Traversable interface.');
        }
        
        $supportedFields = $this->getSupportedToNvpProperties();

        foreach ($nvp as $name => $value) {
            $property = $name;
            $property = strtolower($property);

            if ('shiptostreet2' === $property && in_array($property, $supportedFields)) {
                $this->$property = $value;
                
                continue;
            }

            if ('cvv2' === $property && in_array($property, $supportedFields)) {
                $this->$property = $value;

                continue;
            }

            if ('street2' === $property && in_array($property, $supportedFields)) {
                $this->$property = $value;

                continue;
            }
            
            $property = preg_replace('/\d/', 'nnn', $property, 1);
            
            

            if (false === strpos($name, 'SHIPTOSTREET2')) {
                $property = preg_replace('/\d/', 'mmm', $property, 1);
            }

            $property = strtolower($property);

            if (false == in_array($property, $supportedFields)) {
                continue;
            }

            $matches = array();
            preg_match_all('/\d/', $name, $matches);
            if (array_key_exists(0, $matches) && array_key_exists(0, $matches[0])) {
                if (array_key_exists(1, $matches[0]) && false === strpos($name, 'SHIPTOSTREET2')) {
                    $this->set($property, $value, $matches[0][1], $matches[0][1]);
                } else {
                    $this->set($property, $value, $matches[0][0]);
                }
            } else {
                $this->$property = $value;
            }
        }
    }

    /**
     * @deprecated since 0.3 move the logic to offsetGet
     *
     * @return array
     */
    protected function toNvp()
    {
        $nvp = array();
        foreach ($this->getSupportedToNvpProperties() as $property) {
            $value = $this->$property;
            $name = strtoupper($property);

            if (is_array($value)) {
                foreach ($value as $indexN => $valueN) {
                    //This fixes L_BILLINGAGREEMENTDESCRIPTIONNNN
                    $nameN = strrev(str_replace('NNN', $indexN, strrev($name)));
                    if (is_array($valueN)) {
                        foreach ($valueN as $indexM => $valueM) {
                            $nameM = str_replace('MMM', $indexM, $nameN);
                            $nvp[$nameM] = $valueM;
                        }
                    } else {
                        $nvp[$nameN] = $valueN;
                    }
                }
            } else {
                $nvp[$name] = $value;
            }
        }

        return array_filter($nvp, function($value) {
            return false === is_null($value);
        });
    }

    /**
     * @param string $property
     * @param mixed $value
     * @param int|null $n
     * @param int|null $m
     */
    protected function set($property, $value, $n = null, $m = null)
    {
        $currentValue = $this->$property;
        if (null !== $n && null !== $m) {
            if (false == isset($currentValue[$n])) {
                $currentValue[$n] = array();
            }

            $currentValue[$n][$m] = $value;
        } else if (null !== $n) {
            $currentValue[$n] = $value;
        }

        $this->$property = $currentValue;
    }

    /**
     * @param string $property
     * @param bool $n
     * @param bool $m
     * 
     * @return mixed
     */
    protected function get($property, $n = false, $m = false)
    {
        $currentValue = $this->$property;
        if (false !== $n && false !== $m) {
            if (null === $n && null === $m) {
                return $currentValue;
            }
            if (array_key_exists($n, $currentValue) && array_key_exists($m, $currentValue[$n])) {
                return $currentValue[$n][$m];
            }
        }
        if (null === $n) {
            return $currentValue;
        }
        if (array_key_exists($n, $currentValue)) {
            return $currentValue[$n];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->toNvp());
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->toNvp());
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        $nvp = $this->toNvp();

        return array_key_exists($offset, $nvp) ?
            $nvp[$offset] :
            null
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->fromNvp(array($offset => $value));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        throw new LogicException('Not implemented');
    }
}