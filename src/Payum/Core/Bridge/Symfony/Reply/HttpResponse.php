<?php

namespace Payum\Core\Bridge\Symfony\Reply;

use Payum\Core\Reply\Base;
use Symfony\Component\HttpFoundation\Response;

class HttpResponse extends Base
{
    /**
     * @var Response
     */
    protected $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
