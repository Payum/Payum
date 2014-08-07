<?php
namespace Payum\Bundle\PayumBundle\EventListener;

use Payum\Core\Bridge\Symfony\Reply\HttpResponse as SymfonyHttpResponse;
use Payum\Core\Exception\LogicException;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ReplyToHttpResponseListener
{
    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (false == $event->getException() instanceof ReplyInterface) {
            return;
        }

        $reply = $event->getException();

        if ($reply instanceof SymfonyHttpResponse) {
            $event->setResponse($reply->getResponse());
        } elseif ($reply instanceof HttpResponse) {
            $event->setResponse(new Response($reply->getContent()));
        } elseif ($reply instanceof HttpRedirect) {
            $event->setResponse(new RedirectResponse($reply->getUrl()));
        }

        if ($event->getResponse()) {
            if (false == $event->getResponse()->headers->has('X-Status-Code')) {
                $event->getResponse()->headers->set('X-Status-Code', $event->getResponse()->getStatusCode());
            }

            return;
        }

        $ro = new \ReflectionObject($reply);
        $event->setException(new LogicException(
            sprintf('Cannot convert reply %s to http response.', $ro->getShortName()),
            null,
            $reply
        ));
    }
}
