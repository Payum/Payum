<?php
namespace Payum\Request;

use Payum\Request\InteractiveRequest;

class RedirectUrlInteractiveRequest extends InteractiveRequest
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