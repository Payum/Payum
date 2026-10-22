<?php

namespace Payum\Core\Bridge\Symfony\Reply;

use Payum\Core\Reply\Base;
use Symfony\Component\HttpFoundation\Response;

@trigger_error('The ' . __NAMESPACE__ . '\HttpResponse class is deprecated since version 2.0 and will be removed in 3.0. Use Payum\Bundle\PayumBundle\Reply\HttpResponse from payum/payum-bundle instead.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Use Payum\Bundle\PayumBundle\Reply\HttpResponse from payum/payum-bundle instead.
 */
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

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
