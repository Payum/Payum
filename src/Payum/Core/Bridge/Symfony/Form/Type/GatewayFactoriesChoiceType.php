<?php

namespace Payum\Core\Bridge\Symfony\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

@trigger_error('The ' . __NAMESPACE__ . '\GatewayFactoriesChoiceType class is deprecated since version 2.0 and will be removed in 3.0. Use Payum\Bundle\PayumBundle\Form\Type\GatewayFactoriesChoiceType from payum/payum-bundle instead.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Use Payum\Bundle\PayumBundle\Form\Type\GatewayFactoriesChoiceType from payum/payum-bundle instead.
 */
class GatewayFactoriesChoiceType extends AbstractType
{
    /**
     * @var string[]|callable
     */
    protected $defaultChoices;

    /**
     * @param string[]|callable $defaultChoices
     */
    public function __construct($defaultChoices)
    {
        $this->defaultChoices = $defaultChoices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->defaultChoices,
        ]);
    }

    /**
     * @return ?string
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
