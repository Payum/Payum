<?php

namespace Payum\Core;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpClientInterface
{
    public function send(RequestInterface $request): ResponseInterface;
}
