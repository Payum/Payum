<?php
namespace Payum\Core\Tests\Bridge\Symfony\Action;

use Payum\Core\Bridge\Symfony\Action\ObtainCreditCardAction;
use Payum\Core\Bridge\Symfony\Reply\HttpResponse;
use Payum\Core\Model\CreditCard;
use Payum\Core\PaymentInterface;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Request\RenderTemplate;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

class ObtainCreditCardActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Symfony\Action\ObtainCreditCardAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithFormFactoryAndTemplatingAsArguments()
    {
        new ObtainCreditCardAction($this->createFormFactoryMock(), 'aTemplate');
    }

    /**
     * @test
     */
    public function shouldSupportObtainCreditCardRequest()
    {
        $action = new ObtainCreditCardAction($this->createFormFactoryMock(), 'aTemplate');

        $this->assertTrue($action->supports(new ObtainCreditCard()));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotObtainCreditCardRequest()
    {
        $action = new ObtainCreditCardAction($this->createFormFactoryMock(), 'aTemplate');

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action ObtainCreditCardAction is not supported the request stdClass.
     */
    public function throwIfNotObtainCreditCardRequestGivenOnExecute()
    {
        $action = new ObtainCreditCardAction($this->createFormFactoryMock(), 'aTemplate');

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The action can be run only when http request is set.
     */
    public function throwIfNotSetBeforeExecute()
    {
        $action = new ObtainCreditCardAction($this->createFormFactoryMock(), 'aTemplate');

        $action->execute(new ObtainCreditCard());
    }

    /**
     * @test
     */
    public function shouldRenderFormWhenNotSubmitted()
    {
        $httpRequest = new Request();

        $formView = new FormView();

        $formMock = $this->createFormMock();
        $formMock
            ->expects($this->once())
            ->method('handleRequest')
            ->with($this->identicalTo($httpRequest))
        ;
        $formMock
            ->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(false))
        ;
        $formMock
            ->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($formView))
        ;
        $formMock
            ->expects($this->never())
            ->method('isValid')
        ;

        $formFactoryMock = $this->createFormFactoryMock();
        $formFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with('payum_credit_card')
            ->will($this->returnValue($formMock))
        ;

        $testCase = $this;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\RenderTemplate'))
            ->will($this->returnCallback(function (RenderTemplate $request) use ($testCase, $formView) {
                $testCase->assertEquals('theTemplateName', $request->getTemplateName());
                $testCase->assertEquals(array('form' => $formView), $request->getContext());

                $request->setResult('theObtainCreditCardPageWithForm');
            }))
        ;

        $action = new ObtainCreditCardAction($formFactoryMock, 'theTemplateName');
        $action->setRequest($httpRequest);
        $action->setPayment($paymentMock);

        try {
            $action->execute(new ObtainCreditCard());
        } catch (HttpResponse $e) {
            $this->assertEquals('theObtainCreditCardPageWithForm', $e->getResponse()->getContent());
            $this->assertEquals(200, $e->getResponse()->getStatusCode());
            $this->assertEquals(
                'max-age=0, no-cache, no-store, post-check=0, pre-check=0, private',
                $e->getResponse()->headers->get('Cache-Control')
            );
            $this->assertEquals('no-cache', $e->getResponse()->headers->get('Pragma'));

            return;
        }

        $this->fail('Reply exception was expected to be thrown');
    }

    /**
     * @test
     */
    public function shouldRenderFormWhenSubmittedButNotValid()
    {
        $httpRequest = new Request();

        $formView = new FormView();

        $creditCard = new CreditCard();

        $formMock = $this->createFormMock();
        $formMock
            ->expects($this->once())
            ->method('handleRequest')
            ->with($this->identicalTo($httpRequest))
        ;
        $formMock
            ->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true))
        ;
        $formMock
            ->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($formView))
        ;
        $formMock
            ->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false))
        ;
        $formMock
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($creditCard))
        ;

        $formFactoryMock = $this->createFormFactoryMock();
        $formFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with('payum_credit_card')
            ->will($this->returnValue($formMock))
        ;

        $testCase = $this;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\RenderTemplate'))
            ->will($this->returnCallback(function (RenderTemplate $request) use ($testCase, $formView) {
                $testCase->assertEquals('theTemplateName', $request->getTemplateName());
                $testCase->assertEquals(array('form' => $formView), $request->getContext());

                $request->setResult('theObtainCreditCardPageWithForm');
            }))
        ;

        $action = new ObtainCreditCardAction($formFactoryMock, 'theTemplateName');
        $action->setRequest($httpRequest);
        $action->setPayment($paymentMock);

        try {
            $action->execute(new ObtainCreditCard());
        } catch (HttpResponse $e) {
            $this->assertEquals('theObtainCreditCardPageWithForm', $e->getResponse()->getContent());
            $this->assertEquals(200, $e->getResponse()->getStatusCode());
            $this->assertEquals(
                'max-age=0, no-cache, no-store, post-check=0, pre-check=0, private',
                $e->getResponse()->headers->get('Cache-Control')
            );
            $this->assertEquals('no-cache', $e->getResponse()->headers->get('Pragma'));

            return;
        }

        $this->fail('Reply exception was expected to be thrown');
    }

    /**
     * @test
     */
    public function shouldRenderFormWhenSubmitedAndValid()
    {
        $httpRequest = new Request();

        $creditCard = new CreditCard();

        $formMock = $this->createFormMock();
        $formMock
            ->expects($this->once())
            ->method('handleRequest')
            ->with($this->identicalTo($httpRequest))
        ;
        $formMock
            ->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true))
        ;
        $formMock
            ->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true))
        ;
        $formMock
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($creditCard))
        ;

        $formFactoryMock = $this->createFormFactoryMock();
        $formFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with('payum_credit_card')
            ->will($this->returnValue($formMock))
        ;

        $action = new ObtainCreditCardAction($formFactoryMock, 'aTemplate');
        $action->setRequest($httpRequest);

        $obtainCreditCard = new ObtainCreditCard();

        $action->execute($obtainCreditCard);

        $this->assertSame($creditCard, $obtainCreditCard->obtain());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FormFactoryInterface
     */
    protected function createFormFactoryMock()
    {
        return $this->getMock('Symfony\Component\Form\FormFactoryInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FormInterface
     */
    protected function createFormMock()
    {
        return $this->getMock('Symfony\Component\Form\Form', array(), array(), '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\Core\PaymentInterface');
    }
}
