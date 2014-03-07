<?php
namespace Payum\Bundle\PayumBundle\EventListener;

use Payum\Bundle\PayumBundle\Request\ResponseInteractiveRequest as SymfonyResponseInteractiveRequest;
use Payum\Core\Exception\LogicException;
use Payum\Core\Request\InteractiveRequestInterface;
use Payum\Core\Request\RedirectUrlInteractiveRequest;
use Payum\Core\Request\ResponseInteractiveRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class InteractiveRequestListener
{
    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (false == $event->getException() instanceof InteractiveRequestInterface) {
            return;
        }

        $interactiveRequest = $event->getException();

        if ($interactiveRequest instanceof SymfonyResponseInteractiveRequest) {
            $event->setResponse($interactiveRequest->getResponse());
        } elseif ($interactiveRequest instanceof ResponseInteractiveRequest) {
            $event->setResponse(new Response($interactiveRequest->getContent()));
        } elseif ($interactiveRequest instanceof RedirectUrlInteractiveRequest) {
            $event->setResponse(new RedirectResponse($interactiveRequest->getUrl()));
        }

        if ($event->getResponse()) {
            if (false == $event->getResponse()->headers->has('X-Status-Code')) {
                $event->getResponse()->headers->set('X-Status-Code', $event->getResponse()->getStatusCode());
            }

            return;
        }

        $ro = new \ReflectionObject($interactiveRequest);
        $event->setException(new LogicException(
            sprintf('Cannot convert interactive request %s to symfony response.', $ro->getShortName()),
            null,
            $interactiveRequest
        ));
    }
}
