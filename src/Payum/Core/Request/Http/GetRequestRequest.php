<?php
namespace Payum\Core\Request\Http;

class GetRequestRequest
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

    public function __construct()
    {
        $this->query = array();
        $this->request = array();
        $this->method = '';
        $this->uri = '';
    }
} 