<?php
namespace Payum\Core\Exception\Http;

use Payum\Core\Exception\ExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpExceptionInterface extends ExceptionInterface
{
    public function setRequest(RequestInterface $request): void;

    public function getRequest(): RequestInterface;

    public function setResponse(ResponseInterface $response): void;

    public function getResponse(): ResponseInterface;
}
