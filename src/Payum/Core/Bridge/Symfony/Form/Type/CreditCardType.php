<?php
namespace Payum\Core\Bridge\Symfony\Form\Type;

use Payum\Core\Model\CreditCard;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditCardType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('holder', TextType::class, array('label' => 'form.credit_card.holder'))
            ->add('number', TextType::class, array('label' => 'form.credit_card.number'))
            ->add('securityCode', TextType::class, array('label' => 'form.credit_card.security_code'))
            ->add(
                'expireAt',
                CreditCardExpirationDateType::class,
                array(
                    'input' => 'datetime',
                    'widget' =>'choice',
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
                    'data_class' => CreditCard::class,
                    'validation_groups' => array('Payum'),
                    'label' => false,
                    'translation_domain' => 'PayumBundle',
                )
            );
    }
}
