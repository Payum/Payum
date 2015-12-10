<?php
namespace Payum\Klarna\Invoice;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayFactory;
use Payum\Klarna\Invoice\Action\Api\ActivateAction;
use Payum\Klarna\Invoice\Action\Api\ActivateReservationAction;
use Payum\Klarna\Invoice\Action\Api\CancelReservationAction;
use Payum\Klarna\Invoice\Action\Api\CheckOrderStatusAction;
use Payum\Klarna\Invoice\Action\Api\CreditInvoiceAction;
use Payum\Klarna\Invoice\Action\Api\CreditPartAction;
use Payum\Klarna\Invoice\Action\Api\EmailInvoiceAction;
use Payum\Klarna\Invoice\Action\Api\GetAddressesAction;
use Payum\Klarna\Invoice\Action\Api\PopulateKlarnaFromDetailsAction;
use Payum\Klarna\Invoice\Action\Api\ReserveAmountAction;
use Payum\Klarna\Invoice\Action\Api\ReturnAmountAction;
use Payum\Klarna\Invoice\Action\Api\SendInvoiceAction;
use Payum\Klarna\Invoice\Action\Api\UpdateAction;
use Payum\Klarna\Invoice\Action\AuthorizeAction;
use Payum\Klarna\Invoice\Action\CaptureAction;
use Payum\Klarna\Invoice\Action\RefundAction;
use Payum\Klarna\Invoice\Action\StatusAction;
use Payum\Klarna\Invoice\Action\SyncAction;

class KlarnaInvoiceGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        if (!class_exists('KlarnaCurrency')) {
            throw new \LogicException('You must install "fp/klarna-invoice" library.');
        }

        $config->defaults(array(
            'payum.factory_name' => 'klarna_invoice',
            'payum.factory_title' => 'Klarna Invoice',
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
            'payum.action.api.credit_invoice' => new CreditInvoiceAction(),
            'payum.action.api.credit_part' => new CreditPartAction(),
            'payum.action.api.reserve_amount' => new ReserveAmountAction(),
            'payum.action.api.return_amount' => new ReturnAmountAction(),
            'payum.action.api.email_invoice' => new EmailInvoiceAction(),
            'payum.action.api.send_invoice' => new SendInvoiceAction(),
            'payum.action.api.update' => new UpdateAction(),
        ));

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'eid' => '',
                'secret' => '',
                'country' => '',
                'language' => '',
                'currency' => '',
                'sandbox' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = array('eid', 'secret', 'country', 'language', 'currency');
            $config->defaults(array(
                'sandbox' => true,
            ));

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $config['mode'] = $config['sandbox'] ? \Klarna::BETA : \Klarna::LIVE;

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

                return $klarnaConfig;
            };
        }
    }
}
