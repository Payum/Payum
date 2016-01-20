<?php
namespace Payum\Core\Bridge\Symfony\Form\Type;

use Payum\Core\Model\GatewayConfig;
use Payum\Core\Registry\GatewayFactoryRegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

class GatewayConfigType extends AbstractType
{
    /**
     * @var GatewayFactoryRegistryInterface
     */
    private $registry;

    /**
     * @param GatewayFactoryRegistryInterface $registry
     */
    public function __construct(GatewayFactoryRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gatewayName')
            ->add('factoryName', GatewayFactoriesChoiceType::class)
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

        $form->add('config', FormType::class);
        $configForm = $form->get('config');

        $gatewayFactory = $this->registry->getGatewayFactory($factoryName);
        $config = $gatewayFactory->createConfig();
        $propertyPath = is_array($data) ? '[config]' : 'config';
        $firstTime = false == PropertyAccess::createPropertyAccessor()->getValue($data, $propertyPath);
        foreach ($config['payum.default_options'] as $name => $value) {
            $propertyPath = is_array($data) ? "[config][$name]" : "config[$name]";
            if ($firstTime) {
                PropertyAccess::createPropertyAccessor()->setValue($data, $propertyPath, $value);
            }

            $type = is_bool($value) ? CheckboxType::class : TextType::class;

            $options = array();
            $options['required'] = in_array($name, $config['payum.required_options']);

            $configForm->add($name, $type, $options);
        }

        $event->setData($data);
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => GatewayConfig::class
        ));
    }
}
