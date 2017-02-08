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
     * @param Request|null $httpRequest
     * @deprecated
     */
    public function setHttpRequest(Request $httpRequest = null)
    {
        $this->httpRequest = $httpRequest;
    }

    /**
     * @param RequestStack|null $httpRequestStack
     */
    public function setHttpRequestStack(RequestStack $httpRequestStack = null)
    {
        $this->httpRequestStack = $httpRequestStack;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request GetHttpRequest */
        RequestNotSupportedException::assertSupports($this, $request);

        if ($this->httpRequest instanceof Request) {
            $this->updateRequest($request, $this->httpRequest);
        } elseif ($this->httpRequestStack instanceof RequestStack && null !== $this->httpRequestStack->getMasterRequest()) {
            $this->updateRequest($request, $this->httpRequestStack->getMasterRequest());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof GetHttpRequest;
    }

    /**
     * @param GetHttpRequest $request
     * @param Request $httpRequest
     */
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
