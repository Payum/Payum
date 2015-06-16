<?php

namespace Payum\Core\Bridge\Psr\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Interface ClientInterface
 * @package Payum\Core\Bridge\Psr\Http
 */
interface ClientInterface
{
    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function send(RequestInterface $request);

}
