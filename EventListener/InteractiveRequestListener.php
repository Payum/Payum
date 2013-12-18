<?php
namespace Payum\Bundle\PayumBundle\EventListener;

use Payum\Bundle\PayumBundle\Request\ResponseInteractiveRequest;
use Payum\Core\Exception\LogicException;
use Payum\Core\Request\InteractiveRequestInterface;
use Payum\Core\Request\RedirectUrlInteractiveRequest;
use Payum\Core\Request\RedirectPostInteractiveRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class InteractiveRequestListener
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var string
     */
    protected $redirectPostTemplate;

    public function __construct(\Twig_Environment $twig, $redirectPostTemplate)
    {
        $this->twig = $twig;
        $this->redirectPostTemplate = $redirectPostTemplate;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (false == $event->getException() instanceof InteractiveRequestInterface) {
            return;
        }

        $interactiveRequest = $event->getException();

        if ($interactiveRequest instanceof ResponseInteractiveRequest) {
            $event->setResponse($interactiveRequest->getResponse());
        } elseif ($interactiveRequest instanceof RedirectPostInteractiveRequest) {
            $event->setResponse($this->createPostResponse($interactiveRequest));
        } elseif ($interactiveRequest instanceof RedirectUrlInteractiveRequest) {
            $event->setResponse($this->createRedirectResponse($interactiveRequest));
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

    protected function createPostResponse(RedirectPostInteractiveRequest $interactiveRequest)
    {
        return new Response($this->twig->render($this->redirectPostTemplate, array(
            'url'  => $interactiveRequest->getUrl(),
            'data' => $interactiveRequest->getData()
        )));
    }

    protected function createRedirectResponse(RedirectUrlInteractiveRequest $interactiveRequest)
    {
        return new RedirectResponse($interactiveRequest->getUrl());
    }
}
