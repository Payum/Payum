<?php

namespace Payum\Core\Bridge\Symfony\Form\Type;

use Payum\Core\Model\CreditCard;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

@trigger_error('The '.__NAMESPACE__.'\CreditCardType class is deprecated since version 2.0 and will be removed in 3.0. Use the same class from Payum/PayumBundle instead.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0. Use the same class from Payum/PayumBundle instead.
 */
class CreditCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('holder', TextType::class, [
                'label' => 'form.credit_card.holder',
            ])
            ->add('number', TextType::class, [
                'label' => 'form.credit_card.number',
            ])
            ->add('securityCode', TextType::class, [
                'label' => 'form.credit_card.security_code',
            ])
            ->add(
                'expireAt',
                CreditCardExpirationDateType::class,
                [
                    'input' => 'datetime',
                    'widget' => 'choice',
                    'label' => 'form.credit_card.expire_at',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(
                [
                    'data_class' => CreditCard::class,
                    'validation_groups' => ['Payum'],
                    'label' => false,
                    'translation_domain' => 'PayumBundle',
                ]
            );
    }
}
