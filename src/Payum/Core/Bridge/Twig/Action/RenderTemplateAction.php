<?php

namespace Payum\Core\Bridge\Twig\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\RenderTemplate;
use Twig\Environment;
use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s\RenderTemplateAction class is deprecated and will be removed in 3.0. Use %s instead, which renders through the renderer the application registered.', __NAMESPACE__, \Payum\Core\Action\RenderTemplateAction::class);

/**
 * @deprecated since 2.0, removed in 3.0. Use {@see \Payum\Core\Action\RenderTemplateAction} instead.
 */
class RenderTemplateAction implements ActionInterface
{
    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var string
     */
    protected $layout;

    /**
     * @param string      $layout
     */
    public function __construct(Environment $twig, $layout)
    {
        $this->twig = $twig;
        $this->layout = $layout;
    }

    public function execute($request): void
    {
        /** @var RenderTemplate $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $request->setResult($this->twig->render($request->getTemplateName(), array_replace(
            [
                'layout' => $this->layout,
            ],
            $request->getParameters()
        )));
    }

    public function supports($request)
    {
        return $request instanceof RenderTemplate;
    }
}
