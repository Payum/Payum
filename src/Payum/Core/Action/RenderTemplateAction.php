<?php

declare(strict_types=1);

namespace Payum\Core\Action;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Template\RendererInterface;

/**
 * Answers the 1.x RenderTemplate from the renderer the application registered.
 *
 * This is what keeps an unported gateway working, not somewhere to write new code: a handler names its
 * template by returning a {@see \Payum\Core\Result\NextAction\RenderTemplate} result, which the same
 * renderer resolves.
 *
 * The layout is the renderer's business, so nothing is added to the context here.
 */
class RenderTemplateAction implements ActionInterface
{
    public function __construct(
        private readonly RendererInterface $renderer
    ) {
    }

    /**
     * @param RenderTemplate $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $request->setResult($this->renderer->render($request->getTemplateName(), $request->getParameters()));
    }

    public function supports($request): bool
    {
        return $request instanceof RenderTemplate;
    }
}
