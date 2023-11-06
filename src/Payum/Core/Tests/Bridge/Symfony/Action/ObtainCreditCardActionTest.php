<?php
namespace Payum\Core\Tests\Bridge\Symfony\Action;

use Payum\Core\Bridge\Symfony\Action\ObtainCreditCardAction;
use Payum\Core\Bridge\Symfony\Form\Type\CreditCardType;
use Payum\Core\Bridge\Symfony\Reply\HttpResponse;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Model\CreditCard;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Request\RenderTemplate;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

class ObtainCreditCardActionTest extends TestCase
{
    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(ObtainCreditCardAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportObtainCreditCardRequest()
    {
        $action = new ObtainCreditCardAction($this->createFormFactoryMock(), 'aTemplate');

        $this->assertTrue($action->supports(new ObtainCreditCard()));
    }

    public function testShouldNotSupportAnythingNotObtainCreditCardRequest()
    {
        $action = new ObtainCreditCardAction($this->createFormFactoryMock(), 'aTemplate');

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotObtainCreditCardRequestGivenOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $this->expectExceptionMessage('Action ObtainCreditCardAction is not supported the request stdClass.');
        $action = new ObtainCreditCardAction($this->createFormFactoryMock(), 'aTemplate');

        $action->execute(new \stdClass());
    }

    public function testThrowIfNotSetBeforeExecute()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The action can be run only when http request is set.');
        $action = new ObtainCreditCardAction($this->createFormFactoryMock(), 'aTemplate');

        $action->execute(new ObtainCreditCard());
    }

    public function testShouldRenderFormWhenNotSubmitted()
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
            ->willReturn(false)
        ;
        $formMock
            ->expects($this->once())
            ->method('createView')
            ->willReturn($formView)
        ;
        $formMock
            ->expects($this->never())
            ->method('isValid')
        ;

        $formFactoryMock = $this->createFormFactoryMock();
        $formFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(CreditCardType::class)
            ->willReturn($formMock)
        ;

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\RenderTemplate'))
            ->willReturnCallback(function (RenderTemplate $request) use ($testCase, $formView) {
                $testCase->assertSame('theTemplateName', $request->getTemplateName());
                $testCase->assertEquals(array(
                    'form' => $formView,
                    'model' => null,
                    'firstModel' => null,
                    'actionUrl' => null,
                ), $request->getParameters());

                $request->setResult('theObtainCreditCardPageWithForm');
            })
        ;

        $action = new ObtainCreditCardAction($formFactoryMock, 'theTemplateName');
        $action->setRequest($httpRequest);
        $action->setGateway($gatewayMock);

        try {
            $action->execute(new ObtainCreditCard());
        } catch (HttpResponse $e) {
            $this->assertSame('theObtainCreditCardPageWithForm', $e->getResponse()->getContent());
            $this->assertSame(200, $e->getResponse()->getStatusCode());
            $this->assertSame(
                'max-age=0, no-cache, no-store, post-check=0, pre-check=0, private',
                $e->getResponse()->headers->get('Cache-Control')
            );
            $this->assertSame('no-cache', $e->getResponse()->headers->get('Pragma'));

            return;
        }

        $this->fail('Reply exception was expected to be thrown');
    }

    public function testShouldRenderFormWhenSubmittedButNotValid()
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
            ->willReturn(true)
        ;
        $formMock
            ->expects($this->once())
            ->method('createView')
            ->willReturn($formView)
        ;
        $formMock
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(false)
        ;
        $formMock
            ->expects($this->once())
            ->method('getData')
            ->willReturn($creditCard)
        ;

        $formFactoryMock = $this->createFormFactoryMock();
        $formFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(CreditCardType::class)
            ->willReturn($formMock)
        ;

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\RenderTemplate'))
            ->willReturnCallback(function (RenderTemplate $request) use ($testCase, $formView) {
                $testCase->assertSame('theTemplateName', $request->getTemplateName());
                $testCase->assertEquals(array(
                    'form' => $formView,
                    'model' => null,
                    'firstModel' => null,
                    'actionUrl' => null,
                ), $request->getParameters());

                $request->setResult('theObtainCreditCardPageWithForm');
            })
        ;

        $action = new ObtainCreditCardAction($formFactoryMock, 'theTemplateName');
        $action->setRequest($httpRequest);
        $action->setGateway($gatewayMock);

        try {
            $action->execute(new ObtainCreditCard());
        } catch (HttpResponse $e) {
            $this->assertSame('theObtainCreditCardPageWithForm', $e->getResponse()->getContent());
            $this->assertSame(200, $e->getResponse()->getStatusCode());
            $this->assertSame(
                'max-age=0, no-cache, no-store, post-check=0, pre-check=0, private',
                $e->getResponse()->headers->get('Cache-Control')
            );
            $this->assertSame('no-cache', $e->getResponse()->headers->get('Pragma'));

            return;
        }

        $this->fail('Reply exception was expected to be thrown');
    }

    public function testShouldRenderFormWhenSubmittedAndValid()
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
            ->willReturn(true)
        ;
        $formMock
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true)
        ;
        $formMock
            ->expects($this->once())
            ->method('getData')
            ->willReturn($creditCard)
        ;

        $formFactoryMock = $this->createFormFactoryMock();
        $formFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(CreditCardType::class)
            ->willReturn($formMock)
        ;

        $action = new ObtainCreditCardAction($formFactoryMock, 'aTemplate');
        $action->setRequest($httpRequest);

        $obtainCreditCard = new ObtainCreditCard();

        $action->execute($obtainCreditCard);

        $this->assertSame($creditCard, $obtainCreditCard->obtain());
    }

    public function testShouldPassFirstAndCurrentModelsToTemplate()
    {
        $firstModel = new \stdClass();
        $currentModel = new \stdClass();

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
            ->willReturn(true)
        ;
        $formMock
            ->expects($this->once())
            ->method('createView')
            ->willReturn($formView)
        ;
        $formMock
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(false)
        ;
        $formMock
            ->expects($this->once())
            ->method('getData')
            ->willReturn($creditCard)
        ;

        $formFactoryMock = $this->createFormFactoryMock();
        $formFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(CreditCardType::class)
            ->willReturn($formMock)
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\RenderTemplate'))
            ->willReturnCallback(function (RenderTemplate $request) use ($formView, $firstModel, $currentModel) {
                $this->assertSame('theTemplateName', $request->getTemplateName());
                $this->assertEquals(array(
                    'form' => $formView,
                    'model' => $currentModel,
                    'firstModel' => $firstModel,
                    'actionUrl' => null,
                ), $request->getParameters());

                $request->setResult('theObtainCreditCardPageWithForm');
            })
        ;

        $action = new ObtainCreditCardAction($formFactoryMock, 'theTemplateName');
        $action->setRequest($httpRequest);
        $action->setGateway($gatewayMock);

        try {
            $action->execute(new ObtainCreditCard($firstModel, $currentModel));
        } catch (HttpResponse $e) {
            return;
        }

        $this->fail('Reply exception was expected to be thrown');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FormFactoryInterface
     */
    protected function createFormFactoryMock()
    {
        return $this->createMock('Symfony\Component\Form\FormFactoryInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FormInterface
     */
    protected function createFormMock()
    {
        return $this->createMock('Symfony\Component\Form\Form', array(), array(), '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock('Payum\Core\GatewayInterface');
    }
}
