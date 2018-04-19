<?php
namespace Payum\Core\Bridge\Symfony\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GatewayChoiceType extends AbstractType
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

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => $this->defaultChoices
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
