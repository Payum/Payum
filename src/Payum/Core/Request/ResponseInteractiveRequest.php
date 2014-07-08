<?php
namespace Payum\Core\Request;

/**
 * @deprecated since 0.9 use Payum\Core\Request\Http\ResponseInteractiveRequest
 */
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