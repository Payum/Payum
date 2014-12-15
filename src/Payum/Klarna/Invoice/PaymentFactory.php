<?php
namespace Payum\Klarna\Invoice;

use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Payment;
use Payum\Core\PaymentFactoryInterface;
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

class PaymentFactory implements PaymentFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(array $options = array())
    {
        $options = ArrayObject::ensureArrayObject($options);
        $options->validateNotEmpty(array('eid', 'secret', 'country', 'language', 'currency'));
        $options['mode'] = null === $options['sandbox'] ? \Klarna::BETA : \Klarna::LIVE;
        $options['pClassStorage'] = $options['pClassStorage'] ?: 'json';
        $options['pClassStoragePath'] = $options['pClassStoragePath'] ?: './pclasses.json';
        $options['xmlRpcVerifyHost'] = $options['xmlRpcVerifyHost'] ?: 2;
        $options['xmlRpcVerifyHost'] = null === $options['xmlRpcVerifyPeer'] ? true : $options['xmlRpcVerifyPeer'];

        if (null === $country = \KlarnaCountry::fromCode($options['country'])) {
            throw new LogicException(sprintf('Given %s country code is not valid. Klarna cannot recognize it.', $options['country']));
        }
        if (null === $language = \KlarnaLanguage::fromCode($options['language'])) {
            throw new LogicException(sprintf('Given %s language code is not valid. Klarna cannot recognize it.', $options['language']));
        }
        if (null === $currency = \KlarnaCurrency::fromCode($options['currency'])) {
            throw new LogicException(sprintf('Given %s currency code is not valid. Klarna cannot recognize it.', $options['currency']));
        }

        $config = new Config();
        $config->eid = $options['eid'];
        $config->secret = $options['secret'];
        $config->mode = $options['mode'];
        $config->pClassStorage = $options['pClassStorage'];
        $config->pClassStoragePath = $options['pClassStoragePath'];
        $config->xmlRpcVerifyHost = $options['xmlRpcVerifyHost'];
        $config->xmlRpcVerifyHost = $options['xmlRpcVerifyHost'];

        $config->country = $country;
        $config->language = $language;
        $config->currency = $currency;

        $payment = new Payment;

        $payment->addApi($config);

        $payment->addAction(new CaptureAction);
        $payment->addAction(new AuthorizeAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new SyncAction);
        $payment->addAction(new RefundAction);
        $payment->addAction(new GetHttpRequestAction);

        $payment->addAction(new ActivateAction);
        $payment->addAction(new ActivateReservationAction);
        $payment->addAction(new CancelReservationAction);
        $payment->addAction(new CheckOrderStatusAction);
        $payment->addAction(new GetAddressesAction);
        $payment->addAction(new PopulateKlarnaFromDetailsAction);
        $payment->addAction(new CreditPartAction);
        $payment->addAction(new ReserveAmountAction);

        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction());

        return $payment;
    }
}
