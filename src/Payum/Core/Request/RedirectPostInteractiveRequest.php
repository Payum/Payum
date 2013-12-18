<?php
namespace Payum\Core\Request;

class RedirectPostInteractiveRequest extends RedirectUrlInteractiveRequest
{
    protected $data;

    public function __construct($url, array $data = array())
    {
        parent::__construct($url);
        
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
