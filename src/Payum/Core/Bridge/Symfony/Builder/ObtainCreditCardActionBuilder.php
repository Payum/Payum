<?php

namespace Payum\Core\Bridge\Symfony\Builder;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Symfony\Action\ObtainCreditCardAction;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

@trigger_error('The ' . __NAMESPACE__ . '\ObtainCreditCardActionBuilder class is deprecated since version 2.0 and will be removed in 3.0. Use the same class from Payum/PayumBundle instead.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0. Use the same class from Payum/PayumBundle instead.
 */
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
