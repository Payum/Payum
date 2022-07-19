<?php

namespace Payum\Core\Request;

use InvalidArgumentException;

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
     * @param mixed[] $parameters
     */
    public function __construct($templateName, array $parameters = [])
    {
        $this->templateName = $templateName;
        $this->parameters = $parameters;
        $this->result = '';
    }

    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    /**
     * @return mixed[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function setResult(string $result): void
    {
        $this->result = $result;
    }

    /**
     * @param mixed  $value
     */
    public function setParameter(string $name, $value): void
    {
        $this->parameters[$name] = $value;
    }

    /**
     * @param mixed  $value
     */
    public function addParameter(string $name, $value): void
    {
        if (array_key_exists($name, $this->parameters)) {
            throw new InvalidArgumentException(sprintf('Parameter with given name "%s" already exists', $name));
        }

        $this->parameters[$name] = $value;
    }
}
