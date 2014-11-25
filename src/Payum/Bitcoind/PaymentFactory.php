<?php
namespace Payum\Bitcoind;

use Nbobtc\Bitcoind\BitcoindInterface;
use Payum\Bitcoind\Action\Api\GetNewAddressAction;
use Payum\Bitcoind\Action\CaptureAction;
use Payum\Bitcoind\Action\StatusAction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Twig\Action\RenderTemplateAction;
use Payum\Core\Bridge\Twig\TwigFactory;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\Payment;
use Payum\Core\PaymentInterface;

abstract class PaymentFactory
{
    /**
     * @param BitcoindInterface $bitcoind
     * @param ActionInterface $renderTemplateAction
     * @param string $layoutTemplate
     * @param string $captureTemplate
     *
     * @return PaymentInterface
     */
    public static function createJs(
        BitcoindInterface $bitcoind,
        ActionInterface $renderTemplateAction = null,
        $layoutTemplate = null,
        $captureTemplate = null
    ) {
        $layoutTemplate = $layoutTemplate ?: '@PayumCore/layout.html.twig';
        $captureTemplate = $captureTemplate ?: '@PayumBitcoind/Action/capture.html.twig';
        $renderTemplateAction = $renderTemplateAction ?: new RenderTemplateAction(TwigFactory::createGeneric(), $layoutTemplate);

        $payment = new Payment();

        $payment->addExtension(new EndlessCycleDetectorExtension());

        $payment->addApi($bitcoind);

        $payment->addAction($renderTemplateAction);
        $payment->addAction(new CaptureAction($captureTemplate));
        $payment->addAction(new StatusAction());
        $payment->addAction(new GetNewAddressAction());

        return $payment;
    }

    /**
     */
    private function __construct()
    {
    }
}
