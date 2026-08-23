<?php

declare(strict_types=1);

namespace Payum\Core\Action;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Template\RendererInterface;

/**
 * Answers the 1.x RenderTemplate from the renderer the application registered.
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
