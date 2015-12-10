<?php
namespace Payum\Core\Exception\Http;

use Payum\Core\Exception\ExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpExceptionInterface extends ExceptionInterface
{
    /**
     * @param RequestInterface $request
     *
     * @return void
     */
    public function setRequest(RequestInterface $request);

    /**
     * @return RequestInterface
     */
    public function getRequest();

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public function setResponse(ResponseInterface $response);

    /**
     * @return ResponseInterface
     */
    public function getResponse();
}
