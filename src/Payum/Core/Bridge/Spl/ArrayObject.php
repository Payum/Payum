<?php
namespace Payum\Core\Bridge\Spl;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Security\SensitiveValue;

class ArrayObject extends \ArrayObject
{
    protected $input;

    /**
     * {@inheritDoc}
     */
    public function __construct($input = array(), $flags = 0, $iterator_class = "ArrayIterator")
    {
        if ($input instanceof \ArrayAccess && false == $input instanceof \ArrayObject) {
            $this->input = $input;

            if (false == $input instanceof \Traversable) {
                throw new LogicException('Traversable interface must be implemented in case custom ArrayAccess instance given. It is because some php limitations.');
            }

            $input = iterator_to_array($input);
        }

        parent::__construct($input, $flags, $iterator_class);
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return isset($this[$key]) ? $this[$key] : $default;
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return static
     */
    public function getArray($key, $default = [])
    {
        return static::ensureArrayObject($this->get($key, $default));
    }

    /**
     * @param array|\Traversable $input
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function replace($input)
    {
        if (false == (is_array($input) || $input instanceof \Traversable)) {
            throw new InvalidArgumentException('Invalid input given. Should be an array or instance of \Traversable');
        }

        foreach ($input as $index => $value) {
            $this[$index] = $value;
        }
    }

    /**
     * @param array|\Traversable $input
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function defaults($input)
    {
        if (false == (is_array($input) || $input instanceof \Traversable)) {
            throw new InvalidArgumentException('Invalid input given. Should be an array or instance of \Traversable');
        }

        foreach ($input as $index => $value) {
            if (null === $this[$index]) {
                $this[$index] = $value;
            }
        }
    }

    /**
     * @param array   $required
     * @param boolean $throwOnInvalid
     *
     * @throws LogicException when one of the required fields is empty
     *
     * @return bool
     */
    public function validateNotEmpty($required, $throwOnInvalid = true)
    {
        $required = is_array($required) ? $required : array($required);

        $empty = array();

        foreach ($required as $r) {
            $value = $this[$r];

            if (empty($value)) {
                $empty[] = $r;
            }
        }

        if ($empty && $throwOnInvalid) {
            throw new LogicException(sprintf('The %s fields are required.', implode(', ', $empty)));
        }

        if ($empty) {
            return false;
        }

        return true;
    }

    /**
     * @param array   $required
     * @param boolean $throwOnInvalid
     *
     * @throws \Payum\Core\Exception\LogicException when one of the required fields present
     *
     * @return bool
     */
    public function validatedKeysSet($required, $throwOnInvalid = true)
    {
        $required = is_array($required) ? $required : array($required);

        foreach ($required as $required) {
            if (false == $this->offsetExists($required)) {
                if ($throwOnInvalid) {
                    throw new LogicException(sprintf('The %s fields is not set.', $required));
                }

                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($index, $value)
    {
        if ($this->input) {
            $this->input[$index] = $value;
        }

        return parent::offsetSet($index, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($index)
    {
        if ($this->input) {
            unset($this->input[$index]);
        }

        return parent::offsetUnset($index);
    }

    /**
     * This simply returns NULL when an array does not have this index.
     * It allows us not to do isset all the time we want to access something.
     *
     * {@inheritDoc}
     */
    public function offsetGet($index)
    {
        if ($this->offsetExists($index)) {
            return parent::offsetGet($index);
        }

        return;
    }

    /**
     * @experimental
     *
     * @return array
     */
    public function toUnsafeArray()
    {
        $array = [];
        foreach ($this as $name => $value) {
            if ($value instanceof SensitiveValue) {
                $array[$name] = $value->get();

                continue;
            }

            $array[$name] = $value;
        }

        return $array;
    }

    /**
     * @experimental
     *
     * @return array
     */
    public function toUnsafeArrayWithoutLocal()
    {
        $array = $this->toUnsafeArray();
        unset($array['local']);
        
        return $array;
    }

    /**
     * @param mixed $input
     *
     * @return ArrayObject
     */
    public static function ensureArrayObject($input)
    {
        return $input instanceof static ? $input : new static($input);
    }
}
