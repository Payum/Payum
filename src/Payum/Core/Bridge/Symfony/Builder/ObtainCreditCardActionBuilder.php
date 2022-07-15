<?php

namespace Payum\Core\Bridge\Symfony\Builder;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Symfony\Action\ObtainCreditCardAction;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ObtainCreditCardActionBuilder
{
    private FormFactoryInterface $formFactory;

    private RequestStack $requestStack;

    public function __construct(FormFactoryInterface $formFactory, RequestStack $requestStack)
    {
        $this->formFactory = $formFactory;
        $this->requestStack = $requestStack;
    }

    public function __invoke()
    {
        return call_user_func_array([$this, 'build'], func_get_args());
    }

    /**
     * @return ObtainCreditCardAction
     */
    public function build(ArrayObject $config)
    {
        $action = new ObtainCreditCardAction($this->formFactory, $config['payum.template.obtain_credit_card']);
        $action->setRequestStack($this->requestStack);

        return $action;
    }
}
