<?php

namespace Payum\Core\Bridge\Symfony\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class GetHttpRequestAction implements ActionInterface
{
    /**
     * @var Request
     */
    protected $httpRequest;

    /**
     * @var RequestStack
     */
    protected $httpRequestStack;

    /**
     * @deprecated
     */
    public function setHttpRequest(Request $httpRequest = null)
    {
        $this->httpRequest = $httpRequest;
    }

    public function setHttpRequestStack(RequestStack $httpRequestStack = null)
    {
        $this->httpRequestStack = $httpRequestStack;
    }

    public function execute($request)
    {
        /** @var GetHttpRequest $request */
        RequestNotSupportedException::assertSupports($this, $request);

        if ($this->httpRequest instanceof Request) {
            $this->updateRequest($request, $this->httpRequest);
        } elseif ($this->httpRequestStack instanceof RequestStack) {
            # BC Layer for Symfony 4 (Simplify after support for Symfony < 5 is dropped)
            if (method_exists($this->httpRequestStack, 'getMainRequest')) {
                $mainRequest = $this->httpRequestStack->getMainRequest();
            } else {
                $mainRequest = $this->httpRequestStack->getMasterRequest();
            }
            if (null !== $mainRequest) {
                $this->updateRequest($request, $mainRequest);
            }
        }
    }

    public function supports($request)
    {
        return $request instanceof GetHttpRequest;
    }

    protected function updateRequest(GetHttpRequest $request, Request $httpRequest)
    {
        $request->query = $httpRequest->query->all();
        $request->request = $httpRequest->request->all();
        $request->headers = $httpRequest->headers->all();
        $request->method = $httpRequest->getMethod();
        $request->uri = $httpRequest->getUri();
        $request->clientIp = $httpRequest->getClientIp();
        $request->userAgent = $httpRequest->headers->get('User-Agent');
        $request->content = $httpRequest->getContent();
    }
}
