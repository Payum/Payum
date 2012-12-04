<?php
namespace Payum\Exception\Http;

use Buzz\Message\Response;
use Buzz\Message\Request;

use Payum\Exception\LogicException;

class HttpException extends LogicException
{
    protected $request;

    protected $response;

    public function __construct(Request $model, Response $response, $message = "", $code = 0, \Exception $previous = null)
    {
        $this->request = $model;
        $this->response = $response;

        parent::__construct($message, $code, $previous);
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getRequest()
    {
        return $this->request;
    }
}