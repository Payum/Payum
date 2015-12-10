<?php
namespace Payum\Core\Bridge\Twig\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\RenderTemplate;

class RenderTemplateAction implements ActionInterface
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
     * @param string            $layout
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
        /** @var $request RenderTemplate */
        RequestNotSupportedException::assertSupports($this, $request);

        $request->setResult($this->twig->render($request->getTemplateName(), array_replace(
            array('layout' => $this->layout),
            $request->getParameters()
        )));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof RenderTemplate;
    }
}
