<?php

namespace Payum\Core\Bridge\Symfony\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\RenderTemplate;
use Symfony\Component\Templating\EngineInterface;

@trigger_error('The ' . __NAMESPACE__ . '\RenderTemplateAction class is deprecated since version 2.0 and will be removed in 3.0. Use the same class from Payum/PayumBundle instead.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0. Use the same class from Payum/PayumBundle instead.
 */
class RenderTemplateAction implements ActionInterface
{
    /**
     * @var string
     */
    protected $layout;

    private EngineInterface $templating;

    public function __construct(EngineInterface $templating, $layout = null)
    {
        $this->templating = $templating;
        $this->layout = $layout;
    }

    /**
     * @param mixed $request
     *
     * @throws RequestNotSupportedException if the action dose not support the request.
     */
    public function execute($request): void
    {
        /** @var RenderTemplate $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $request->setResult(
            $this->templating->render(
                $request->getTemplateName(),
                array_replace(
                    [
                        'layout' => $this->layout,
                    ],
                    $request->getParameters()
                )
            )
        );
    }

    /**
     * @param mixed $request
     *
     * @return boolean
     */
    public function supports($request)
    {
        return $request instanceof RenderTemplate;
    }
}
