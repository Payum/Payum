<?php
namespace Payum\Core\Bridge\Symfony\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;
use Symfony\Component\HttpFoundation\Request;

class GetHttpRequestAction implements ActionInterface
{
    /**
     * @var Request
     */
    protected $httpRequest;

    /**
     * @param Request|null $httpRequest
     */
    public function setHttpRequest(Request $httpRequest = null)
    {
        $this->httpRequest = $httpRequest;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request GetHttpRequest */
        RequestNotSupportedException::assertSupports($this, $request);

        if (false == $this->httpRequest) {
            return;
        }

        $request->query = $this->httpRequest->query->all();
        $request->request = $this->httpRequest->request->all();
        $request->headers = $this->httpRequest->headers->all();
        $request->method = $this->httpRequest->getMethod();
        $request->uri = $this->httpRequest->getUri();
        $request->clientIp = $this->httpRequest->getClientIp();
        $request->userAgent = $this->httpRequest->headers->get('User-Agent');
        $request->content = $this->httpRequest->getContent();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof GetHttpRequest;
    }
}
