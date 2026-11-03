<?php

namespace Payum\Core\Bridge\Symfony\Reply;

use Payum\Core\Reply\Base;
use Symfony\Component\HttpFoundation\Response;
use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s class is deprecated and will be removed in 3.0. Use Payum\Bundle\PayumBundle\Reply\HttpResponse from payum/payum-bundle instead.', HttpResponse::class);

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
