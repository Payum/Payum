<?php
namespace Payum\Core\Request;

class RenderTemplate
{
    /**
     * @var string
     */
    protected $templateName;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var string
     */
    protected $result;

    /**
     * @param string $templateName
     * @param array  $parameters
     */
    public function __construct($templateName, array $parameters = array())
    {
        $this->templateName = $templateName;
        $this->parameters = $parameters;
        $this->result = '';
    }

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param string $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function addParameter($name, $value)
    {
        if (array_key_exists($name, $this->parameters)) {
            throw new \InvalidArgumentException(sprintf('Parameter with given name "%s" already exists', $name));
        }

        $this->parameters[$name] = $value;
    }
}
