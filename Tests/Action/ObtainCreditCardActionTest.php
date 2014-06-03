<?php
namespace Payum\Bundle\PayumBundle\Tests\Action;

use Payum\Bundle\PayumBundle\Action\ObtainCreditCardAction;
use Payum\Core\Bridge\Symfony\Request\ResponseInteractiveRequest;
use Payum\Core\Model\CreditCard;
use Payum\Core\Request\ObtainCreditCardRequest;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;

class ObtainCreditCardActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\Action\ObtainCreditCardAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithFormFactoryAndTemplatingAsArguments()
    {
        new ObtainCreditCardAction($this->createFormFactoryMock(), $this->createTemplatingMock());
    }

    /**
     * @test
     */
    public function shouldSupportObtainCreditCardRequest()
    {
        $action = new ObtainCreditCardAction($this->createFormFactoryMock(), $this->createTemplatingMock());

        $this->assertTrue($action->supports(new ObtainCreditCardRequest));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotObtainCreditCardRequest()
    {
        $action = new ObtainCreditCardAction($this->createFormFactoryMock(), $this->createTemplatingMock());

        $this->assertFalse($action->supports(new \stdClass));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action ObtainCreditCardAction is not supported the request stdClass.
     */
    public function throwIfNotObtainCreditCardRequestGivenOnExecute()
    {
        $action = new ObtainCreditCardAction($this->createFormFactoryMock(), $this->createTemplatingMock());

        $action->execute(new \stdClass);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The action can be run only when http request is set.
     */
    public function throwIfNotSetBeforeExecute()
    {
        $action = new ObtainCreditCardAction($this->createFormFactoryMock(), $this->createTemplatingMock());

        $action->execute(new ObtainCreditCardRequest);
    }

    /**
     * @test
     */
    public function shouldRenderFormWhenNotSubmited()
    {
        $httpRequest = new Request;

        $formView = new FormView;

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

        $templatingMock = $this->createTemplatingMock();
        $templatingMock
            ->expects($this->once())
            ->method('render')
            ->with('PayumBundle:Action:obtainCreditCard.html.twig', array('form' => $formView))
            ->will($this->returnValue('theObtainCreditCardPageWithForm'))
        ;

        $action = new ObtainCreditCardAction($formFactoryMock, $templatingMock);
        $action->setRequest($httpRequest);

        try {
            $action->execute(new ObtainCreditCardRequest);
        } catch (ResponseInteractiveRequest $e) {
            $this->assertEquals('theObtainCreditCardPageWithForm', $e->getResponse()->getContent());
            $this->assertEquals(200, $e->getResponse()->getStatusCode());
            $this->assertEquals(
                'max-age=0, no-cache, no-store, post-check=0, pre-check=0, private',
                $e->getResponse()->headers->get('Cache-Control')
            );
            $this->assertEquals('no-cache', $e->getResponse()->headers->get('Pragma'));


            return;
        }

        $this->fail('ResponseInteractiveRequest exception was expected to be thrown');
    }

    /**
     * @test
     */
    public function shouldRenderFormWhenSubmitedButNotValid()
    {
        $httpRequest = new Request;

        $formView = new FormView;

        $creditCard = new CreditCard;

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

        $templatingMock = $this->createTemplatingMock();
        $templatingMock
            ->expects($this->once())
            ->method('render')
            ->with('PayumBundle:Action:obtainCreditCard.html.twig', array('form' => $formView))
            ->will($this->returnValue('theObtainCreditCardPageWithForm'))
        ;

        $action = new ObtainCreditCardAction($formFactoryMock, $templatingMock);
        $action->setRequest($httpRequest);

        try {
            $action->execute(new ObtainCreditCardRequest);
        } catch (ResponseInteractiveRequest $e) {
            $this->assertEquals('theObtainCreditCardPageWithForm', $e->getResponse()->getContent());
            $this->assertEquals(200, $e->getResponse()->getStatusCode());
            $this->assertEquals(
                'max-age=0, no-cache, no-store, post-check=0, pre-check=0, private',
                $e->getResponse()->headers->get('Cache-Control')
            );
            $this->assertEquals('no-cache', $e->getResponse()->headers->get('Pragma'));


            return;
        }

        $this->fail('ResponseInteractiveRequest exception was expected to be thrown');
    }

    /**
     * @test
     */
    public function shouldRenderFormWhenSubmitedAndValid()
    {
        $httpRequest = new Request;

        $creditCard = new CreditCard;

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

        $templatingMock = $this->createTemplatingMock();
        $templatingMock
            ->expects($this->never())
            ->method('render')
        ;

        $action = new ObtainCreditCardAction($formFactoryMock, $templatingMock);
        $action->setRequest($httpRequest);

        $obtainCreditCard = new ObtainCreditCardRequest;

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
     * @return \PHPUnit_Framework_MockObject_MockObject|EngineInterface
     */
    protected function createTemplatingMock()
    {
        return $this->getMock('Symfony\Component\Templating\EngineInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FormInterface
     */
    protected function createFormMock()
    {
        return $this->getMock('Symfony\Component\Form\Form', array(), array(), '', false);
    }
}