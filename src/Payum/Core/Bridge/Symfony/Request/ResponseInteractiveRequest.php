<?php
namespace Payum\Core\Bridge\Symfony\Request;

use Payum\Core\Request\BaseInteractiveRequest;
use Symfony\Component\HttpFoundation\Response;

class ResponseInteractiveRequest extends BaseInteractiveRequest
{
    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $response;

    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}