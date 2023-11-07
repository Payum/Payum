<?php

namespace Payum\Core\Bridge\Symfony\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\RenderTemplate;
use Symfony\Component\Templating\EngineInterface;

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
    public function execute($request)
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
