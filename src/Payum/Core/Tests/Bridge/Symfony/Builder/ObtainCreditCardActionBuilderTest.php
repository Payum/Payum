<?php
namespace Payum\Core\Tests\Bridge\Symfony\Builder;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Symfony\Action\ObtainCreditCardAction;
use Payum\Core\Bridge\Symfony\Builder\ObtainCreditCardActionBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ObtainCreditCardActionBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testCouldBeConstructedWithFormFactoryAndRequestStackServices()
    {
        new ObtainCreditCardActionBuilder(
            $this->getMock(FormFactoryInterface::class),
            $this->getMock(RequestStack::class)
        );
    }

    public function testShouldBuildObtainCreditCardWithGivenTemplate()
    {
        $formFactory = $this->getMock(FormFactoryInterface::class);
        $requestStack = $this->getMock(RequestStack::class);

        $builder = new ObtainCreditCardActionBuilder($formFactory, $requestStack);

        $action = $builder->build(new ArrayObject([
            'payum.template.obtain_credit_card' => 'obtain_credit_card_template',
        ]));

        $this->assertInstanceOf(ObtainCreditCardAction::class, $action);

        $this->assertAttributeSame($formFactory, 'formFactory', $action);
        $this->assertAttributeSame($requestStack, 'httpRequestStack', $action);
        $this->assertAttributeSame('obtain_credit_card_template', 'templateName', $action);
    }

    public function testAllowUseBuilderAsAsFunction()
    {
        $formFactory = $this->getMock(FormFactoryInterface::class);
        $requestStack = $this->getMock(RequestStack::class);

        $builder = new ObtainCreditCardActionBuilder($formFactory, $requestStack);

        $action = $builder(new ArrayObject([
            'payum.template.obtain_credit_card' => 'obtain_credit_card_template',
        ]));

        $this->assertInstanceOf(ObtainCreditCardAction::class, $action);
    }
}
