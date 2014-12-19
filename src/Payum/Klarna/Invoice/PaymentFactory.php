<?php
namespace Payum\Klarna\Invoice;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Payment;
use Payum\Core\PaymentFactory as CorePaymentFactory;
use Payum\Klarna\Invoice\Action\Api\ActivateAction;
use Payum\Klarna\Invoice\Action\Api\ActivateReservationAction;
use Payum\Klarna\Invoice\Action\Api\CancelReservationAction;
use Payum\Klarna\Invoice\Action\Api\CheckOrderStatusAction;
use Payum\Klarna\Invoice\Action\Api\CreditPartAction;
use Payum\Klarna\Invoice\Action\Api\GetAddressesAction;
use Payum\Klarna\Invoice\Action\Api\PopulateKlarnaFromDetailsAction;
use Payum\Klarna\Invoice\Action\Api\ReserveAmountAction;
use Payum\Klarna\Invoice\Action\AuthorizeAction;
use Payum\Klarna\Invoice\Action\CaptureAction;
use Payum\Klarna\Invoice\Action\RefundAction;
use Payum\Klarna\Invoice\Action\StatusAction;
use Payum\Klarna\Invoice\Action\SyncAction;

class PaymentFactory extends CorePaymentFactory
{
    /**
     * {@inheritDoc}
     */
    protected function build(Payment $payment, ArrayObject $config)
    {
        $config->validateNotEmpty(array('eid', 'secret', 'country', 'language', 'currency'));

        $config->defaults(array(
            'sandbox' => true,
            'pClassStorage' => 'json',
            'pClassStoragePath' => './pclasses.json',
            'xmlRpcVerifyHost' => 2,
            'xmlRpcVerifyPeer' => true,

            'payum.action.capture' => new CaptureAction(),
            'payum.action.authorize' => new AuthorizeAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.sync' => new SyncAction(),
            'payum.action.refund' => new RefundAction(),

            'payum.action.api.activate' => new ActivateAction(),
            'payum.action.api.activate_reservation' => new ActivateReservationAction(),
            'payum.action.api.cancel_reservation' => new CancelReservationAction(),
            'payum.action.api.check_order_status' => new CheckOrderStatusAction(),
            'payum.action.api.get_addresses' => new GetAddressesAction(),
            'payum.action.api.populate_klarna_from_details' => new PopulateKlarnaFromDetailsAction(),
            'payum.action.api.credit_part' => new CreditPartAction(),
            'payum.action.api.reserve_amount' => new ReserveAmountAction(),
        ));


        $config['mode'] = null === $config['sandbox'] ? \Klarna::BETA : \Klarna::LIVE;

        if (null === $country = \KlarnaCountry::fromCode($config['country'])) {
            throw new LogicException(sprintf('Given %s country code is not valid. Klarna cannot recognize it.', $config['country']));
        }
        if (null === $language = \KlarnaLanguage::fromCode($config['language'])) {
            throw new LogicException(sprintf('Given %s language code is not valid. Klarna cannot recognize it.', $config['language']));
        }
        if (null === $currency = \KlarnaCurrency::fromCode($config['currency'])) {
            throw new LogicException(sprintf('Given %s currency code is not valid. Klarna cannot recognize it.', $config['currency']));
        }

        $klarnaConfig = new Config();
        $klarnaConfig->eid = $config['eid'];
        $klarnaConfig->secret = $config['secret'];
        $klarnaConfig->mode = $config['mode'];
        $klarnaConfig->country = $country;
        $klarnaConfig->language = $language;
        $klarnaConfig->currency = $currency;
        $klarnaConfig->pClassStorage = $config['pClassStorage'];
        $klarnaConfig->pClassStoragePath = $config['pClassStoragePath'];
        $klarnaConfig->xmlRpcVerifyHost = $config['xmlRpcVerifyHost'];
        $klarnaConfig->xmlRpcVerifyHost = $config['xmlRpcVerifyHost'];
        $config['payum.api'] = $klarnaConfig;
    }
}
