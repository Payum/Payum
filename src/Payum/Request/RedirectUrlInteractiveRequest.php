<?php
namespace Payum\Request;

use Payum\Request\BaseInteractiveRequest;

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