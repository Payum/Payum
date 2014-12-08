<?php
namespace Payum\Bundle\PayumBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CreditCardExpirationDateType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ('choice' == $options['widget']) {
            if (empty($view['day']->vars['value'])) {
                $view['day']->vars['value'] = $view['day']->vars['choices'][0]->value;
            }

            $style = 'display:none';
            if (false == empty($view['day']->vars['attr']['style'])) {
                $style = $view['day']->vars['attr']['style'].'; '.$style;
            }

            $view['day']->vars['attr']['style'] = $style;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'years' => range(date('Y'), date('Y') + 9)
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return 'date';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'payum_credit_card_expiration_date';
    }
}
