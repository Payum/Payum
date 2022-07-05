<?php

namespace Payum\Core;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpClientInterface
{
    /**
     * @return ResponseInterface
     */
    public function send(RequestInterface $request);
}
