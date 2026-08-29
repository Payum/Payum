<?php

namespace Payum\Core\Reply;

/**
 * @deprecated since 2.0.0, will be removed in 3.0. A notify handler answers the PSP with a
 *             Payum\Core\Result\Acknowledgement on its Payum\Core\Result\NotifyResult; a handler with a
 *             page to show returns Payum\Core\Result\NextAction\RenderTemplate.
 */
class HttpResponse extends Base
{
    /**
     * @var string
     */
    protected $content;

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var string[]
     */
    protected $headers;

    /**
     * @param string   $content
     * @param int      $statusCode
     * @param string[] $headers
     */
    public function __construct($content, $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return string[]
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
