<?php
namespace Payum\Core\Bridge\Symfony\Form\Type;

use Payum\Core\Registry\PaymentFactoryRegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class PaymentConfigType extends AbstractType
{
    /**
     * @var PaymentFactoryRegistryInterface
     */
    private $registry;

    /**
     * @param PaymentFactoryRegistryInterface $registry
     */
    public function __construct(PaymentFactoryRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('paymentName')
            ->add('factoryName', 'payum_payment_factories_choice')
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
        if (is_null($data)) {
            return;
        }

        $propertyPath = is_array($data) ? '[factoryName]' : 'factoryName';
        $factoryName = PropertyAccess::createPropertyAccessor()->getValue($data, $propertyPath);
        if (empty($factoryName)) {
            return;
        }

        $form = $event->getForm();

        $form->add('config', 'form');
        $configForm = $form->get('config');

        $paymentFactory = $this->registry->getPaymentFactory($factoryName);
        $config = $paymentFactory->createConfig();
        foreach ($config['payum.default_options'] as $name => $value) {
            $isRequired = in_array($name, $config['payum.required_options']);
            $configForm->add($name, is_bool($value) ? 'checkbox' : 'text', array(
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
            'data_class' => 'Payum\Core\Model\PaymentConfig'
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
