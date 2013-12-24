<?php
namespace Payum\Core\Request;

class RedirectUrlInteractiveRequest extends BaseInteractiveRequest
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @param string $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}