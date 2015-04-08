<?php
namespace Payum\Bundle\PayumBundle\Sonata;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\FormFactoryInterface;

class GatewayConfigAdmin extends Admin
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @param FormFactoryInterface $formFactory
     */
    public function setFormFactory(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $form)
    {
        $form->reorder(array()); //hack!
    }

    /**
     * {@inheritDoc}
     */
    protected function configureListFields(ListMapper $list)
    {
        $list
            ->add('gatewayName')
            ->add('factoryName')
            ->add('config', 'array')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getFormBuilder()
    {
        $formBuilder = $this->formFactory->createBuilder('payum_gateway_config', $this->getSubject(), array(
            'data_class' => get_class($this->getSubject()),
        ));

        $this->defineFormBuilder($formBuilder);

        return $formBuilder;
    }
}