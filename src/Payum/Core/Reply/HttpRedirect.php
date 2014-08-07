<?php
namespace Payum\Core\Reply;

class HttpRedirect extends Base
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @param string $content
     */
    public function __construct($content)
    {
        $this->url = $content;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}