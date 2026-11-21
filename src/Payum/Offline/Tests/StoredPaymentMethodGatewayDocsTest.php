<?php

declare(strict_types=1);

namespace Payum\Offline\Tests;

use Http\Discovery\Psr17FactoryDiscovery;
use League\Uri\Uri;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\DeletePaymentMethodCommand;
use Payum\Core\Command\Initiator;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Command\SavePaymentMethodCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Exception\UnsupportedOperationException;
use Payum\Core\Gateway;
use Payum\Core\Gateway\Capability;
use Payum\Core\Gateway\DeclaresCapabilities;
use Payum\Core\Gateway\GatewayInterface;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\DeletePaymentMethodHandlerInterface;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Handler\SavePaymentMethodHandlerInterface;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Model\Customer;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentMethodType;
use Payum\Core\Model\StoredPaymentMethod;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Reference\InMemoryReferenceIndex;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\DeletePaymentMethodResult;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\SavePaymentMethodResult;
use Payum\Core\Result\StoredPaymentMethodDetails;
use Payum\Core\Result\StoredPaymentMethodStatus;
use Payum\Core\Security\TokenPurpose;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Runs the handlers of docs/gateways/stored-payment-methods.md against a fake PSP.
 */
final class StoredPaymentMethodGatewayDocsTest extends TestCase
{
    /**
     * @var Payum<object>
     */
    private Payum $payum;

    private Customer $customer;

    protected function setUp(): void
    {
        $_SERVER['HTTP_HOST'] = 'shop.example.com';
        DocsAcmeApi::$requests = [];
        DocsAcmeApi::$setupConfirmed = false;
        DocsAcmeNotifyHandler::$reference = null;

        $this->payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->setReferenceIndex(new InMemoryReferenceIndex())
            ->addGlobalService(ServerRequestInterface::class, Psr17FactoryDiscovery::findServerRequestFactory()
                ->createServerRequest('POST', 'https://shop.example.com/webhooks/acme'))
            ->registerGateway('acme', new DocsAcmeConfig())
            ->getPayum();

        $this->customer = new Customer();
        $this->customer->setCustomerId('customer-42');
        $this->payum->getStorage(Customer::class)->update($this->customer);
    }

    public function testShouldDeclareTheCapabilities(): void
    {
        $gateway = $this->payum->getGateway('acme');
        $this->assertInstanceOf(Gateway::class, $gateway);
        $capabilities = $gateway->capabilities();

        foreach ([Capability::StoredPaymentMethods, Capability::Recurring, Capability::SavePaymentMethod, Capability::DeletePaymentMethod] as $capability) {
            $this->assertContains($capability, $capabilities);
        }
    }

    public function testShouldSaveDuringTheFirstChargeAndChargeTheReferenceLater(): void
    {
        $method = $this->method();
        $gateway = $this->payum->getGateway('acme');

        $gateway->execute(CaptureCommand::forPayment($this->payment($method), initiator: Initiator::Customer));

        $this->assertTrue(DocsAcmeApi::$requests[0]['save_payment_method']);
        $this->assertFalse(DocsAcmeApi::$requests[0]['off_session']);
        $this->assertSame('pm_1', $method->getReference());
        $this->assertSame('4242', $method->getLast4());
        $this->assertSame(StoredPaymentMethodStatus::Active, $method->getStatus());
        $this->assertSame('cus_1', $this->customer->getGatewayCustomerId('acme'));

        $result = $gateway->execute(CaptureCommand::forPayment($this->payment($method)));

        $this->assertSame('ch_1', $result->transactionId);
        $this->assertSame('pm_1', DocsAcmeApi::$requests[1]['payment_method']);
        $this->assertSame('cus_1', DocsAcmeApi::$requests[1]['customer']);
        $this->assertTrue(DocsAcmeApi::$requests[1]['off_session']);
        $this->assertArrayHasKey('payum_reference', DocsAcmeApi::$requests[1]['metadata']);
    }

    public function testShouldRefuseAMerchantChargeWithoutRecurring(): void
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('acme', new DocsAcmeConfig())
            ->registerGateway('no_recurring', new DocsAcmeNoRecurringConfig())
            ->getPayum();
        $method = $this->method();
        $method->apply(StoredPaymentMethodDetails::active('pm_1'));
        $payment = $this->payment($method);

        $this->expectException(UnsupportedOperationException::class);

        $payum->getGateway('no_recurring')->execute(CaptureCommand::forPayment($payment));
    }

    public function testShouldSaveAMethodAcrossTheRedirect(): void
    {
        $method = $this->method();
        $token = $this->payum->getTokenIssuer()->create(TokenPurpose::SavePaymentMethod, 'acme', $method, 'saved.php');

        $first = $this->payum->savePaymentMethod(Request::create($token->getTargetUrl()));
        $this->assertInstanceOf(RedirectResponse::class, $first);
        $this->assertSame('https://acme.test/setup/seti_1', $first->getTargetUrl());

        DocsAcmeApi::$setupConfirmed = true;
        $second = $this->payum->savePaymentMethod(Request::create($token->getTargetUrl()));
        $this->assertInstanceOf(RedirectResponse::class, $second);
        $this->assertSame($token->getAfterUrl(), $second->getTargetUrl());

        $identity = $this->payum->getStorage(StoredPaymentMethod::class)->identify($method);
        $saved = $this->payum->getStorage(StoredPaymentMethod::class)->find($identity->getId());
        $this->assertInstanceOf(StoredPaymentMethod::class, $saved);
        $this->assertSame('pm_2', $saved->getReference());
        $this->assertSame(StoredPaymentMethodStatus::Active, $saved->getStatus());
        $this->assertSame($token->getTargetUrl(), DocsAcmeApi::$requests[0]['return_url']);
    }

    public function testShouldDeleteAMethod(): void
    {
        $method = $this->method();
        $method->apply(StoredPaymentMethodDetails::active('pm_1'));

        $result = $this->payum->getGateway('acme')->execute(DeletePaymentMethodCommand::forMethod($method));

        $this->assertFalse($result->isFailed());
        $this->assertSame([
            'detach' => 'pm_1',
        ], DocsAcmeApi::$requests[0]);
        $this->assertSame(StoredPaymentMethodStatus::Revoked, $method->getStatus());
    }

    public function testAWebhookShouldUpdateTheMethodItNames(): void
    {
        $method = $this->method();
        $this->payum->getGateway('acme')->execute(SavePaymentMethodCommand::forMethod($method));
        DocsAcmeApi::$setupConfirmed = true;
        $this->payum->getGateway('acme')->execute(SavePaymentMethodCommand::forMethod($method));
        DocsAcmeNotifyHandler::$reference = 'pm_2';

        $this->payum->getGateway('acme')->execute(NotifyCommand::forGateway());

        $identity = $this->payum->getStorage(StoredPaymentMethod::class)->identify($method);
        $updated = $this->payum->getStorage(StoredPaymentMethod::class)->find($identity->getId());
        $this->assertInstanceOf(StoredPaymentMethod::class, $updated);
        $this->assertSame('1111', $updated->getLast4());
        $this->assertSame(2031, $updated->getExpiryYear());
        $this->assertSame(StoredPaymentMethodStatus::Active, $updated->getStatus());
    }

    private function method(): StoredPaymentMethod
    {
        $method = new StoredPaymentMethod();
        $method->setGatewayName('acme');
        $method->setCustomer($this->customer);
        $this->payum->getStorage(StoredPaymentMethod::class)->update($method);

        return $method;
    }

    private function payment(StoredPaymentMethod $method): Payment
    {
        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(1000);
        $payment->setCustomer($this->customer);
        $payment->setStoredPaymentMethod($method);
        $this->payum->getStorage(Payment::class)->update($payment);

        return $payment;
    }
}

final class DocsAcmeApi
{
    /**
     * @var list<array<string, mixed>>
     */
    public static array $requests = [];

    public static bool $setupConfirmed = false;

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function charge(array $request): array
    {
        self::$requests[] = $request;

        return [
            'id' => 'ch_1',
            'customer' => 'cus_1',
            'network_transaction_id' => 'ntx_1',
            'payment_method' => [
                'id' => 'pm_1',
                'brand' => 'visa',
                'last4' => '4242',
                'exp_month' => 12,
                'exp_year' => 2030,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function createSetup(array $request): array
    {
        self::$requests[] = $request;

        return [
            'id' => 'seti_1',
            'url' => 'https://acme.test/setup/seti_1',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function findSetup(string $id): array
    {
        if (! self::$setupConfirmed) {
            throw new RuntimeException('The customer has not confirmed ' . $id);
        }

        return [
            'id' => $id,
            'customer' => 'cus_1',
            'payment_method' => [
                'id' => 'pm_2',
                'last4' => '4242',
            ],
        ];
    }

    public function detach(string $paymentMethod): void
    {
        self::$requests[] = [
            'detach' => $paymentMethod,
        ];
    }
}

class DocsAcmeConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return DocsAcmeGateway::class;
    }
}

final class DocsAcmeNoRecurringConfig extends DocsAcmeConfig
{
    public function getGatewayClass(): string
    {
        return DocsAcmeNoRecurringGateway::class;
    }
}

class DocsAcmeGateway implements GatewayInterface, DeclaresCapabilities
{
    public function handlers(): array
    {
        return [
            DocsAcmeCaptureHandler::class,
            DocsAcmeSavePaymentMethodHandler::class,
            DocsAcmeDeletePaymentMethodHandler::class,
            DocsAcmeNotifyHandler::class,
        ];
    }

    public function capabilities(): array
    {
        return [
            Capability::StoredPaymentMethods,
            Capability::Recurring,
        ];
    }

    public function configClass(): string
    {
        return DocsAcmeConfig::class;
    }

    public function logo(): Logo
    {
        return Url::create('https://acme.test/logo.svg');
    }

    public function name(): string
    {
        return 'Acme';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://acme.test');
    }
}

final class DocsAcmeNoRecurringGateway extends DocsAcmeGateway
{
    public function capabilities(): array
    {
        return [Capability::StoredPaymentMethods];
    }

    public function configClass(): string
    {
        return DocsAcmeNoRecurringConfig::class;
    }
}

final class DocsAcmeCaptureHandler implements CaptureHandlerInterface
{
    public function __construct(
        private readonly DocsAcmeApi $api,
    ) {
    }

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        $amount = $context->amount();
        $credential = $context->storedCredential();

        $request = [
            'amount' => (int) $amount?->getAmount(),
            'currency' => $amount?->getCurrency()->getCode(),
            'metadata' => [
                'payum_reference' => $context->merchantReference(),
            ],
        ];

        if (null !== $credential) {
            $request['customer'] = $context->customer()?->getGatewayCustomerId((string) $context->gatewayName());
            $request['off_session'] = Initiator::Merchant === $credential->initiator;

            if ($credential->isFirst) {
                $request['save_payment_method'] = true;
            } else {
                $request['payment_method'] = $context->storedPaymentMethod()?->getReference();
            }
        }

        $charge = $this->api->charge($request);
        $result = CaptureResult::captured($charge['id']);

        if (true !== $credential?->isFirst) {
            return $result;
        }

        return $result->withStoredPaymentMethod(StoredPaymentMethodDetails::active(
            $charge['payment_method']['id'],
            type: PaymentMethodType::Card,
            brand: $charge['payment_method']['brand'],
            last4: $charge['payment_method']['last4'],
            expiryMonth: $charge['payment_method']['exp_month'],
            expiryYear: $charge['payment_method']['exp_year'],
            networkTransactionId: $charge['network_transaction_id'],
            gatewayCustomerId: $charge['customer'],
        ));
    }
}

final class DocsAcmeSavePaymentMethodHandler implements SavePaymentMethodHandlerInterface
{
    public function __construct(
        private readonly DocsAcmeApi $api,
    ) {
    }

    public function handle(SavePaymentMethodCommand $command, Context $context): SavePaymentMethodResult
    {
        $state = $context->state();

        if (! isset($state['setup_id'])) {
            $setup = $this->api->createSetup([
                'customer' => $context->customer()?->getGatewayCustomerId((string) $context->gatewayName()),
                'return_url' => $context->token()?->getTargetUrl(),
            ]);
            $state['setup_id'] = $setup['id'];

            return SavePaymentMethodResult::pending(new Redirect($setup['url']));
        }

        $setup = $this->api->findSetup($state['setup_id']);

        return SavePaymentMethodResult::saved(StoredPaymentMethodDetails::active(
            $setup['payment_method']['id'],
            type: PaymentMethodType::Card,
            last4: $setup['payment_method']['last4'],
            gatewayCustomerId: $setup['customer'],
        ), $setup['id']);
    }
}

final class DocsAcmeDeletePaymentMethodHandler implements DeletePaymentMethodHandlerInterface
{
    public function __construct(
        private readonly DocsAcmeApi $api,
    ) {
    }

    public function handle(DeletePaymentMethodCommand $command, Context $context): DeletePaymentMethodResult
    {
        $this->api->detach((string) $command->method()->getReference());

        return DeletePaymentMethodResult::deleted();
    }
}

final class DocsAcmeNotifyHandler implements NotifyHandlerInterface
{
    public static ?string $reference = null;

    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        $event = [
            'id' => 'evt_1',
            'type' => 'payment_method.updated',
            'data' => [
                'payment_method' => self::$reference,
                'last4' => '1111',
                'exp_month' => 1,
                'exp_year' => 2031,
            ],
        ];

        return WebhookEvent::verified($event, $event['id'], $event['type'], reference: $event['data']['payment_method']);
    }

    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        if ('payment_method.updated' !== $event->type || null === $context->storedPaymentMethod()) {
            return NotifyResult::ignored();
        }

        return NotifyResult::handled()->withStoredPaymentMethod(new StoredPaymentMethodDetails(
            last4: $event->payload['data']['last4'],
            expiryMonth: $event->payload['data']['exp_month'],
            expiryYear: $event->payload['data']['exp_year'],
        ));
    }
}
