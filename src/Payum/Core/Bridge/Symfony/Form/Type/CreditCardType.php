<?php
namespace Payum\Core\Bridge\Symfony\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CreditCardType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('holder', 'text', array('label' => 'form.credit_card.holder'))
            ->add('number', 'text', array('label' => 'form.credit_card.number'))
            ->add('securityCode', 'text', array('label' => 'form.credit_card.security_code'))
            ->add(
                'expireAt',
                'payum_credit_card_expiration_date',
                array(
                    'input' => 'datetime',
                    'widget' => 'choice',
                    'label' => 'form.credit_card.expire_at',
                )
            );
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                array(
                    'data_class' => 'Payum\Core\Model\CreditCard',
                    'validation_groups' => array('Payum'),
                    'label' => false,
                    'translation_domain' => 'PayumBundle',
                )
            );
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'payum_credit_card';
    }
}
