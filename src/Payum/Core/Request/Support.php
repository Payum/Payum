<?php
namespace Payum\Core\Request;

class Support
{
    /**
     * @var mixed
     */
    protected $request;

    /**
     * @var boolean
     */
    protected $isSupported;

    /**
     * @param mixed $request
     */
    public function __construct($request)
    {
        $this->request = $request;
        $this->isSupported = false;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return boolean
     */
    public function isSupported()
    {
        return $this->isSupported;
    }

    /**
     * @param boolean $bool
     */
    public function setSupported($bool)
    {
        $this->isSupported = !!$bool;
    }
}
