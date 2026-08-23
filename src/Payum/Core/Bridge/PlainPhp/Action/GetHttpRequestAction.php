<?php

namespace Payum\Core\Bridge\PlainPhp\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;
use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s\GetHttpRequestAction class is deprecated and will be removed in 3.0. Use %s instead, which reads the PSR-7 request the container holds.', __NAMESPACE__, \Payum\Core\Action\GetHttpRequestAction::class);

/**
 * @deprecated since 2.0, removed in 3.0. Use {@see \Payum\Core\Action\GetHttpRequestAction} instead.
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
