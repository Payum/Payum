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
    protected $context;

    /**
     * @var string
     */
    protected $result;

    /**
     * @param string $templateName
     * @param array  $context
     */
    public function __construct($templateName, array $context = array())
    {
        $this->templateName = $templateName;
        $this->context = $context;
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
    public function getContext()
    {
        return $this->context;
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
}
