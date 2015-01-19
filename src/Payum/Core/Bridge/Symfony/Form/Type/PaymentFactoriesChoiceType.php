<?php
namespace Payum\Core\Bridge\Symfony\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PaymentFactoriesChoiceType extends AbstractType
{
    /**
     * @var string[]
     */
    protected $defaultChoices;

    /**
     * @param string[] $defaultChoices
     */
    public function __construct(array $defaultChoices)
    {
        $this->defaultChoices = $defaultChoices;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
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
        return 'choice';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'payum_payment_factories_choice';
    }
}
