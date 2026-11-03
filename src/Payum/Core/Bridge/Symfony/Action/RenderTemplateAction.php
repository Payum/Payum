<?php

namespace Payum\Core\Bridge\Symfony\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\RenderTemplate;
use Symfony\Component\Templating\EngineInterface;
use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s class is deprecated and will be removed in 3.0. Use Payum\Bundle\PayumBundle\Action\RenderTemplateAction from payum/payum-bundle instead.', RenderTemplateAction::class);

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Use Payum\Bundle\PayumBundle\Action\RenderTemplateAction from payum/payum-bundle instead.
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
     * @return bool
     */
    public function supports($request)
    {
        return $request instanceof RenderTemplate;
    }
}
