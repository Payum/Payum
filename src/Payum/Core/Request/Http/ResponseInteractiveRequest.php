<?php
namespace Payum\Core\Request\Http;

use Payum\Core\Request\BaseInteractiveRequest;

class ResponseInteractiveRequest extends BaseInteractiveRequest
{
    /**
     * @var string
     */
    protected $content;

    /**
     * @param string $content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}