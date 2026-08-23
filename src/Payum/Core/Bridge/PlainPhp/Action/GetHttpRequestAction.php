<?php

namespace Payum\Core\Bridge\PlainPhp\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Handler\Context;
use Payum\Core\Request\GetHttpRequest;
use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s\GetHttpRequestAction class is deprecated and will be removed in 3.0. Stop registering it: core answers GetHttpRequest on its own. Port the action dispatching it to a handler, which reads the request from %s::httpRequest().', __NAMESPACE__, Context::class);

/**
 * @deprecated since 2.0, removed in 3.0. Core answers `GetHttpRequest` on its own, so remove this
 *             action. Port the action dispatching it to a handler and read
 *             {@see \Payum\Core\Handler\Context::httpRequest()}.
 */
class GetHttpRequestAction implements ActionInterface
{
    /**
     * @param GetHttpRequest $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $request->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $request->query = $_GET;
        $request->request = $_REQUEST;
        $request->clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
        $request->uri = $_SERVER['REQUEST_URI'] ?? '';
        $request->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $request->content = file_get_contents('php://input');
    }

    public function supports($request)
    {
        return $request instanceof GetHttpRequest;
    }
}
