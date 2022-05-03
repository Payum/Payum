<?php
namespace Payum\Core\Request;

class GetHttpRequest
{
    public array $query;

    public array $request;

    public string $method;

    public string $uri;

    public string $clientIp;

    public string $userAgent;

    public string $content;

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
