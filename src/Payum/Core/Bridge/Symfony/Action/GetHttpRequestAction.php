<?php

namespace Payum\Core\Bridge\Symfony\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s class is deprecated and will be removed in 3.0. Use Payum\Bundle\PayumBundle\Action\GetHttpRequestAction from payum/payum-bundle instead.', GetHttpRequestAction::class);

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Use Payum\Bundle\PayumBundle\Action\GetHttpRequestAction from payum/payum-bundle instead.
 */
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
     * @deprecated since 2.0.0, will be removed in 3.0. Use setHttpRequestStack() instead.
     */
    public function setHttpRequest(?Request $httpRequest = null): void
    {
        $this->httpRequest = $httpRequest;
    }

    public function setHttpRequestStack(?RequestStack $httpRequestStack = null): void
    {
        $this->httpRequestStack = $httpRequestStack;
    }

    public function execute($request): void
    {
        /** @var GetHttpRequest $request */
        RequestNotSupportedException::assertSupports($this, $request);

        if ($this->httpRequest instanceof Request) {
            $this->updateRequest($request, $this->httpRequest);
        } elseif ($this->httpRequestStack instanceof RequestStack) {
            $mainRequest = $this->httpRequestStack->getMainRequest();
            if (null !== $mainRequest) {
                $this->updateRequest($request, $mainRequest);
            }
        }
    }

    public function supports($request)
    {
        return $request instanceof GetHttpRequest;
    }

    protected function updateRequest(GetHttpRequest $request, Request $httpRequest): void
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
