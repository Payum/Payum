<?php
namespace Payum\Bundle\PayumBundle\Sonata;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\FormFactoryInterface;

class PaymentConfigAdmin extends Admin
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
        $form->reorder([]); //hack!
    }

    /**
     * {@inheritDoc}
     */
    protected function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier('paymentName')
            ->add('factoryName')
            ->add('config', 'array')
            ->add('_action', 'actions', [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ]
            ])
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getFormBuilder()
    {
        $formBuilder = $this->formFactory->createBuilder('payum_payment_config', $this->getSubject(), array(
            'data_class' => get_class($this->getSubject()),
        ));

        $this->defineFormBuilder($formBuilder);

        return $formBuilder;
    }
}