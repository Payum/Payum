<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\Form\Type;

use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;
use Payum\Core\Model\CreditCardInterface;
use Symfony\Component\Form\FormFactoryInterface;

class CreditCardTypeTest extends WebTestCase
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
        $form = $this->formFactory->create('payum_credit_card');
        $view = $form->createView();

        $this->assertInstanceOf('Symfony\Component\Form\FormInterface', $form);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $view);
    }

    /**
     * @test
     */
    public function shouldSubmitDataCorrectly()
    {
        $form = $this->formFactory->create('payum_credit_card', null, array(
            'csrf_protection' => false,
        ));

        $form->submit(array(
            'holder' => 'John Doe',
            'number' => '4111111111111111',
            'securityCode' => '123',
            'expireAt' => array(
                'day' => 1,
                'month' => 10,
                'year' => 2020,
            ),
        ));

        $this->assertTrue($form->isValid(), $form->getErrors(true, false));

        /** @var CreditCardInterface $card */
        $card = $form->getData();

        $this->assertInstanceOf('Payum\Core\Model\CreditCardInterface', $card);

        $this->assertEquals('John Doe', $card->getHolder());
        $this->assertEquals('4111111111111111', $card->getNumber());
        $this->assertEquals('123', $card->getSecurityCode());
        $this->assertEquals('2020-10-31', $card->getExpireAt()->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function shouldRequireHolderNotBlank()
    {
        $form = $this->formFactory->create('payum_credit_card', null, array(
            'csrf_protection' => false,
        ));

        $form->submit(array(
            'holder' => '',
            'number' => '4111111111111111',
            'securityCode' => '123',
            'expireAt' => array(
                'day' => 1,
                'month' => 10,
                'year' => 2020,
            ),
        ));

        $this->assertFalse($form->isValid());
        $this->assertFalse($form->get('holder')->isValid());
    }

    /**
     * @test
     */
    public function shouldRequireNumberNotBlank()
    {
        $form = $this->formFactory->create('payum_credit_card', null, array(
            'csrf_protection' => false,
        ));

        $form->submit(array(
            'holder' => 'John Doe',
            'number' => '',
            'securityCode' => '123',
            'expireAt' => array(
                'day' => 1,
                'month' => 10,
                'year' => 2020,
            ),
        ));

        $this->assertFalse($form->isValid());
        $this->assertFalse($form->get('number')->isValid());
    }

    /**
     * @test
     */
    public function shouldNumberPassLuchValidation()
    {
        $form = $this->formFactory->create('payum_credit_card', null, array(
            'csrf_protection' => false,
        ));

        $form->submit(array(
            'holder' => 'John Doe',
            'number' => '1234',
            'securityCode' => '123',
            'expireAt' => array(
                'day' => 1,
                'month' => 10,
                'year' => 2020,
            ),
        ));

        $this->assertFalse($form->isValid());
        $this->assertFalse($form->get('number')->isValid());
    }

    /**
     * @test
     */
    public function shouldRequireSecurityCodeNotBlank()
    {
        $form = $this->formFactory->create('payum_credit_card', null, array(
            'csrf_protection' => false,
        ));

        $form->submit(array(
            'holder' => 'John Doe',
            'number' => '4111111111111111',
            'securityCode' => '',
            'expireAt' => array(
                'day' => 1,
                'month' => 10,
                'year' => 2020,
            ),
        ));

        $this->assertFalse($form->isValid());
        $this->assertFalse($form->get('securityCode')->isValid());
    }

    /**
     * @test
     */
    public function shouldRequireExpireAtNotBlank()
    {
        $form = $this->formFactory->create('payum_credit_card', null, array(
            'csrf_protection' => false,
        ));

        $form->submit(array(
            'holder' => 'John Doe',
            'number' => '4111111111111111',
            'securityCode' => '',
            'expireAt' => array(
                'day' => '',
                'month' => '',
                'year' => '',
            ),
        ));

        $this->assertFalse($form->isValid());
        $this->assertFalse($form->get('expireAt')->isValid());
    }

    /**
     * @test
     */
    public function shouldRequireExpireAtInFuture()
    {
        $form = $this->formFactory->create('payum_credit_card', null, array(
            'csrf_protection' => false,
        ));

        $form->submit(array(
            'holder' => 'John Doe',
            'number' => '4111111111111111',
            'securityCode' => '',
            'expireAt' => array(
                'day' => '1',
                'month' => '1',
                'year' => '1970',
            ),
        ));

        $this->assertFalse($form->isValid());
        $this->assertFalse($form->get('expireAt')->isValid());
    }
}
