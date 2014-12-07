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

class PaymentBuilder implements PaymentBuilderInterface
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
        $this
            ->set('buzz', 'client', ClientFactory::createCurl())
            ->set('twig', 'environment', TwigFactory::createGeneric())
            ->set('payum.templates', 'layout', '@PayumCore/layout.html.twig')

            ->setAction('execute_same_request_with_details', new ExecuteSameRequestWithModelDetailsAction())
            ->setAction('generic_order', new GenericOrderAction())
            ->setAction('capture_order', new CaptureOrderAction())
            ->setAction('get_http_request', new GetHttpRequestAction())
            ->setAction('render_template', new RenderTemplateAction(
                $this->get('twig', 'environment'),
                $this->get('payum.templates', 'layout')
            ))

            ->setExtension('endless_cycle_detector', new EndlessCycleDetectorExtension)

            ->setBuilder('core', function(Payment $payment, PaymentBuilderInterface $builder) {
                if (false == empty($this->values['payum.required_options'])) {
                    ArrayObject::ensureArrayObject($builder->get('payum.options'))->validateNotEmpty(
                        $builder->get('payum.required_options')
                    );
                }

                foreach (array_reverse($builder->get('payum.actions')) as $action) {
                    $payment->addAction($action);
                }

                foreach (array_reverse($builder->get('payum.apis')) as $api) {
                    $payment->addApi($api);
                }

                foreach (array_reverse($builder->get('payum.extensions')) as $extension) {
                    $payment->addExtension($extension);
                }
            })
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function setAction($name, ActionInterface $action)
    {
        $this->set('payum.actions', $name, $action);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setApi($name, $api)
    {
        $this->set('payum.apis', $name, $api);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setExtension($name, ExtensionInterface $extension)
    {
        $this->set('payum.extensions', $name, $extension);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setBuilder($name, \Closure $func)
    {
        $this->set('payum.builders', $name, $func);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function set($namespace, $name, $value)
    {
        $this->values[$namespace][$name] = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function get($namespace, $name = null, $default = null)
    {
        if ($name) {
            return isset($this->values[$namespace][$name]) ? $this->values[$namespace][$name] : $default;
        }

        return isset($this->values[$namespace]) ? $this->values[$namespace] : [];
    }

    /**
     * {@inheritDoc}
     */
    public function getPayment()
    {
        $payment = new Payment();

        foreach ($this->get('payum.builders') as $func) {
            $func($payment, $this);
        }

        return $payment;
    }
}