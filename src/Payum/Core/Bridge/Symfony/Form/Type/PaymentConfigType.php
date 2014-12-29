<?php
namespace Payum\Core\Bridge\Symfony\Form\Type;

use Payum\Core\PaymentFactoryInterface;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory as PaypalExpressCheckoutPaymentFactory;
use Payum\Stripe\JsPaymentFactory as StripeJsPaymentFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints\NotBlank;

class PaymentConfigType extends AbstractType
{
    /**
     * @var PaymentFactoryInterface[]
     */
    protected $factories = array();

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->factories = array(
            'paypal_express_checkout_nvp' => new PaypalExpressCheckoutPaymentFactory(),
            'stripe_js' => new StripeJsPaymentFactory()
        );

        $builder
            ->add('paymentName', null, array(
                'constraints' => array(new NotBlank),
            ))
            ->add('factoryName', 'choice', array(
                'choices' => array(
                    'paypal_express_checkout_nvp' => 'Paypal ExpressCheckout',
                    'stripe_js' => 'Stripe.Js',
                ),
                'empty_data' => false,
                'constraints' => array(new NotBlank),
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'buildCredentials'));
        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'buildCredentials'));
    }

    /**
     * @param FormEvent $event
     */
    public function buildCredentials(FormEvent $event)
    {
        /** @var array $data */
        $data = $event->getData();

        $propertyPath = is_array($data) ? '[factoryName]' : 'factoryName';
        $factoryName = PropertyAccess::createPropertyAccessor()->getValue($data, $propertyPath);
        if (empty($factoryName)) {
            return;
        }

        $form = $event->getForm();

        $form->add('config', 'form');
        $configForm = $form->get('config');

        $paymentFactory = $this->factories[$factoryName];
        $config = $paymentFactory->createConfig();
        foreach ($config['options.default'] as $name => $value) {
            $isRequired = in_array($name, $config['options.required']);
            $configForm->add($name, is_bool($value) ? 'checkbox' : 'text', array(
                'constraints' => array_filter(array(
                    $isRequired ? new NotBlank : null
                )),
                'empty_data' => $value,
                'required' => $isRequired,
            ));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Payum\Core\Model\PaymentConfigInterface'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'payum_payment_config';
    }
}
