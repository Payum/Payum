<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Model;

use Payum\Exception\InvalidArgumentException;
use Payum\Exception\LogicException;

/**
 * @deprecated since 0.6.3 and will be removed in 0.7
 */
abstract class BaseModel implements \ArrayAccess, \IteratorAggregate
{
    /**
     * @var array
     */
    protected $others = array();

    /**
     * @return array
     */
    abstract protected function getSupportedToNvpProperties();

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

        $nvp = array_replace($this->others, $nvp);

        return new \ArrayIterator(array_filter($nvp, function($value) {
            return false === is_null($value);
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, iterator_to_array($this->getIterator()));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        $nvp = iterator_to_array($this->getIterator());

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
        $supportedFields = $this->getSupportedToNvpProperties();

        $property = $offset;
        $property = strtolower($property);

        if ('shiptostreet2' === $property && in_array($property, $supportedFields)) {
            $this->$property = $value;

            return;
        }

        if ('cvv2' === $property && in_array($property, $supportedFields)) {
            $this->$property = $value;

            return;
        }

        if ('street2' === $property && in_array($property, $supportedFields)) {
            $this->$property = $value;

            return;
        }

        $property = preg_replace('/\d/', 'nnn', $property, 1);

        if (false === strpos($offset, 'SHIPTOSTREET2')) {
            $property = preg_replace('/\d/', 'mmm', $property, 1);
        }

        $property = strtolower($property);

        $matches = array();
        preg_match_all('/\d/', $offset, $matches);
        if (array_key_exists(0, $matches) && array_key_exists(0, $matches[0])) {
            if (array_key_exists(1, $matches[0]) && false === strpos($offset, 'SHIPTOSTREET2')) {
                $this->set($property, $value, $matches[0][1], $matches[0][1]);
            } else {
                $this->set($property, $value, $matches[0][0]);
            }
        } else if (property_exists(get_class($this), $property)) {
            $this->$property = $value;
        } else {
            $this->others[$offset] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->offsetSet($offset, null);
    }
}