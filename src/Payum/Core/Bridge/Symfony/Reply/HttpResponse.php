<?php
namespace Payum\Core\Bridge\Symfony\Reply;

use Payum\Core\Reply\Base;
use Symfony\Component\HttpFoundation\Response;

class HttpResponse extends Base
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
