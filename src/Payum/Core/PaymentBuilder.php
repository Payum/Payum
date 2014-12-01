<?php
namespace Payum\Core;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\CaptureOrderAction;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GenericOrderAction;
use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Bridge\Buzz\ClientFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Twig\Action\RenderTemplateAction;
use Payum\Core\Bridge\Twig\TwigFactory;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\Extension\ExtensionInterface;

class PaymentBuilder
{
    /**
     * @var array
     */
    private $values = [
        'payum.options' => [],
        'payum.required_options' => [],
        'payum.actions' => [],
        'payum.apis' => [],
        'payum.extensions' => []
    ];

    public function __construct()
    {
        $this->set('buzz', 'client', ClientFactory::createCurl());
        $this->set('twig', 'environment', TwigFactory::createGeneric());

        $this->set('payum.templates', 'layout', '@PayumCore/layout.html.twig');

        $this->setAction('execute_same_request_with_details', new ExecuteSameRequestWithModelDetailsAction());
        $this->setAction('generic_order', new GenericOrderAction());
        $this->setAction('capture_order', new CaptureOrderAction());
        $this->setAction('get_http_request', new GetHttpRequestAction());
        $this->setAction('render_template', new RenderTemplateAction(
            $this->get('twig', 'environment'),
            $this->get('payum.templates', 'layout')
        ));

        $this->setExtension('endless_cycle_detector', new EndlessCycleDetectorExtension);
    }

    public function setAction($name, ActionInterface $action)
    {
        $this->set('oayum.actions', $name, $action);
    }

    public function setApi($name, $api)
    {
        $this->set('payum.apis', $name, $api);
    }

    public function setExtension($name, ExtensionInterface $extension)
    {
        $this->set('payum.extensions', $name, $extension);
    }

    public function set($namespace, $name, $value)
    {
        $this->values[$namespace][$name] = $value;
    }

    public function get($namespace, $name, $default = null)
    {
        return isset($this->values[$namespace][$name]) ? $this->values[$namespace][$name] : $default;
    }

    public function getPayment()
    {
        $payment = new Payment();

        $this->buildPayment($payment);

        return $payment;
    }

    protected function buildPayment(Payment $payment)
    {
        if (false == empty($this->values['payum.required_options'])) {
            ArrayObject::ensureArrayObject($this->values['payum.options'])->validateNotEmpty(
                $this->values['payum.required_options']
            );
        }

        foreach (array_reverse($this->values['payum.actions']) as $action) {
            $payment->addAction($action);
        }

        foreach (array_reverse($this->values['payum.apis']) as $api) {
            $payment->addApi($api);
        }

        foreach (array_reverse($this->values['payum.extensions']) as $extension) {
            $payment->addExtension($extension);
        }
    }
}