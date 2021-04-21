<?php
namespace Payum\Core\Request;

class GetHttpRequest
{
    /**
     * @var array
     */
    public $query;

    /**
     * @var array
     */
    public $request;

    /**
     * @var string
     */
    public $method;

    /**
     * @var string
     */
    public $uri;

    /**
     * @var string
     */
    public $clientIp;

    /**
     * @var string
     */
    public $userAgent;

    /**
     * @var string
     */
    public $content;

    public function __construct()
    {
        $this->query = array();
        $this->request = array();
        $this->method = '';
        $this->uri = '';
        $this->clientIp = '';
        $this->userAgent = '';
        $this->content = '';
    }
}
