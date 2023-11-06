<?php
namespace Payum\Core\Bridge\PlainPhp\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;

class GetHttpRequestAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param GetHttpRequest $request
     */
    public function execute($request)
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

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof GetHttpRequest;
    }
}
