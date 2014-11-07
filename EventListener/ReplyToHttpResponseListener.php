<?php
namespace Payum\Bundle\PayumBundle\EventListener;

use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Reply\ReplyInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ReplyToHttpResponseListener
{
    /**
     * @var ReplyToSymfonyResponseConverter
     */
    private $replyToSymfonyResponseConverter;

    /**
     * @param ReplyToSymfonyResponseConverter $replyToSymfonyResponseConverter
     */
    public function __construct(ReplyToSymfonyResponseConverter $replyToSymfonyResponseConverter)
    {
        $this->replyToSymfonyResponseConverter = $replyToSymfonyResponseConverter;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (false == $event->getException() instanceof ReplyInterface) {
            return;
        }

        $response = $this->replyToSymfonyResponseConverter->convert($event->getException());
        if (false == $response->headers->has('X-Status-Code')) {
            $response->headers->set('X-Status-Code', $response->getStatusCode());
        }

        $event->setResponse($response);
    }
}
