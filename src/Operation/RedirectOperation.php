<?php
namespace Paymnt\Operation;

class RedirectOperation implements InteractiveOperationInterface
{
    protected $redirectUrl;
    
    public function __construct($redirectUrl)
    {
        $this->redirectUrl;
    }
    
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }
}