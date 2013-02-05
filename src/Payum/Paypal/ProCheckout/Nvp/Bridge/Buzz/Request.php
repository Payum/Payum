<?php
namespace Payum\Paypal\ProCheckout\Nvp\Bridge\Buzz;

use Buzz\Message\Request as BaseRequest;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class Request extends BaseRequest
{
    /**
     * @param array $values
     * @param string $prefix
     * @param string $format
     *
     * @return array
     */
    protected function flattenArray(array $values, $prefix = '', $format = '%s')
    {
        $flat = array();

        foreach ($values as $name => $value) {
            $flatName = $prefix.sprintf($format, $name);

            if (is_array($value)) {
                $flat += $this->flattenArray($value, $flatName, '[%s]');
            } else {
                $flat[$flatName] = $value;
            }
        }

        return $flat;
    }

    /**
     * @var array
     */
    private $fields = array();

    /**
     * Sets the value of a form field.
     *
     * If the value is an array it will be flattened and one field value will
     * be added for each leaf.
     */
    public function setField($name, $value)
    {
        if (is_array($value)) {
            $this->addFields(array($name => $value));
            return;
        }

        if ('[]' == substr($name, -2)) {
            $this->fields[substr($name, 0, -2)][] = $value;
        } else {
            $this->fields[$name] = $value;
        }
    }

    /**
     * @param array $fields
     */
    public function addFields(array $fields)
    {
        foreach ($this->flattenArray($fields) as $name => $value) {
            $this->setField($name, $value);
        }
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        $this->fields = array();
        $this->addFields($fields);
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }


    /**
     * @return string
     */
    public function getContent()
    {
        $paramList = array();
        foreach($this->fields as $index => $value) {
            $paramList[] = $index . "[" . strlen($value) . "]=" . $value;
        }

        return implode("&", $paramList);
    }
}
