<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\Form\Type;

use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;
use Symfony\Component\Form\FormFactoryInterface;

class CreditCardExpirationDateTypeTest extends WebTestCase
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    protected function setUp()
    {
        parent::setUp();

        $this->formFactory = $this->container->get('form.factory');
    }

    /**
     * @test
     */
    public function couldBeCreatedByFormFactory()
    {
        $form = $this->formFactory->create('payum_credit_card_expiration_date');
        $view = $form->createView();

        $this->assertInstanceOf('Symfony\Component\Form\FormInterface', $form);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $view);
    }

    /**
     * @test
     */
    public function shouldAllowSubmitExpireDateAsChoice()
    {
        $form = $this->formFactory->create('payum_credit_card_expiration_date', null, array(
            'widget' => 'choice',
            'input' => 'datetime',
            'csrf_protection' => false,
        ));

        $form->submit(array(
            'day' => 1,
            'month' => 1,
            'year' => 2020,
        ));

        $this->assertTrue($form->isValid(), $form->getErrors(true, false));

        $data = $form->getData();
        $this->assertInstanceOf('DateTime', $data);
        $this->assertEquals('2020-01-01', $data->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function shouldHideDaySelectAndSetFirstDayFromChoiceAsValue()
    {
        $form = $this->formFactory->create('payum_credit_card_expiration_date', null, array(
            'widget' => 'choice',
            'input' => 'datetime',
            'csrf_protection' => false,
        ));

        $view = $form->createView();

        $this->assertEquals(array('style' => 'display:none'), $view['day']->vars['attr']);
        $this->assertEquals(1, $view['day']->vars['value']);
    }

    /**
     * @test
     */
    public function shouldHideDaySelectAndSetDayFromGivenDateTimeAsValue()
    {
        $date = new \DateTime('2020-01-10');

        $form = $this->formFactory->create('payum_credit_card_expiration_date', $date, array(
            'widget' => 'choice',
            'input' => 'datetime',
            'csrf_protection' => false,
        ));

        $view = $form->createView();

        $this->assertEquals(array('style' => 'display:none'), $view['day']->vars['attr']);
        $this->assertEquals(10, $view['day']->vars['value']);
    }
}