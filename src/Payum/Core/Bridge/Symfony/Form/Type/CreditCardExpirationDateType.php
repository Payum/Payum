<?php

namespace Payum\Core\Bridge\Symfony\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s class is deprecated and will be removed in 3.0. Use Payum\Bundle\PayumBundle\Form\Type\CreditCardExpirationDateType from payum/payum-bundle instead.', CreditCardExpirationDateType::class);

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Use Payum\Bundle\PayumBundle\Form\Type\CreditCardExpirationDateType from payum/payum-bundle instead.
 */
class CreditCardExpirationDateType extends AbstractType
{
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if ('choice' === $options['widget']) {
            if (empty($view['day']->vars['value'])) {
                $view['day']->vars['value'] = $view['day']->vars['choices'][0]->value;
            }

            $style = 'display:none';
            if (! empty($view['day']->vars['attr']['style'])) {
                $style = $view['day']->vars['attr']['style'] . '; ' . $style;
            }

            $view['day']->vars['attr']['style'] = $style;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'max_expiration_year' => date('Y') + 10,
            'min_expiration_year' => date('Y'),
            'years' => fn (Options $options) => range($options['min_expiration_year'], $options['max_expiration_year']),
        ]);
    }

    public function getParent(): ?string
    {
        return DateType::class;
    }
}
