<?php
namespace Payum\Request;

class RedirectUrlInteractiveRequest extends BaseInteractiveRequest
{
    protected $url;
    
    public function __construct($url)
    {
        $this->url = $url;
    }
    
    public function getUrl()
    {
        return $this->url;
    }
}