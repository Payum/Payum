<?php
namespace Payum\Core\Request;

class RenderTemplate
{
    protected string $templateName;

    protected array $parameters;

    protected string $result;

    public function __construct(string $templateName, array $parameters = [])
    {
        $this->templateName = $templateName;
        $this->parameters = $parameters;
        $this->result = '';
    }

    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function setResult(string $result)
    {
        $this->result = $result;
    }

    public function setParameter(string $name, mixed$value)
    {
        $this->parameters[$name] = $value;
    }

    public function addParameter(string $name, mixed $value)
    {
        if (array_key_exists($name, $this->parameters)) {
            throw new \InvalidArgumentException(sprintf('Parameter with given name "%s" already exists', $name));
        }

        $this->parameters[$name] = $value;
    }
}
