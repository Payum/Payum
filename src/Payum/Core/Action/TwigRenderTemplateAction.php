<?php
namespace Payum\Core\Action;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\RenderTemplateRequest;

class TwigRenderTemplateAction implements ActionInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var string
     */
    protected $layout;

    /**
     * @param \Twig_Environment $twig
     * @param string $layout
     */
    public function __construct(\Twig_Environment $twig, $layout)
    {
        $this->twig = $twig;
        $this->layout = $layout;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request RenderTemplateRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $request->setResult($this->twig->render($request->getTemplateName(), array_replace(
            array('layout' => $this->layout),
            $request->getContext()
        )));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof RenderTemplateRequest;
    }
}
