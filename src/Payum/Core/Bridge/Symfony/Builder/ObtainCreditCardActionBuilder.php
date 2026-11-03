<?php

namespace Payum\Core\Bridge\Symfony\Builder;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Symfony\Action\ObtainCreditCardAction;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s class is deprecated and will be removed in 3.0. Use Payum\Bundle\PayumBundle\Builder\ObtainCreditCardActionBuilder from payum/payum-bundle instead.', ObtainCreditCardActionBuilder::class);

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Use Payum\Bundle\PayumBundle\Builder\ObtainCreditCardActionBuilder from payum/payum-bundle instead.
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
        return call_user_func_array($this->build(...), func_get_args());
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
