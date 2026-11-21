<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use Http\Discovery\Psr17FactoryDiscovery;
use League\Uri\Uri;
use Payum\Core\Command\DeletePaymentMethodCommand;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Command\SavePaymentMethodCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway;
use Payum\Core\Gateway\Capability;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\DeletePaymentMethodHandlerInterface;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Handler\SavePaymentMethodHandlerInterface;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Model\StoredPaymentMethod;
use Payum\Core\Model\StoredPaymentMethodInterface;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Reference\InMemoryReferenceIndex;
use Payum\Core\Result\DeletePaymentMethodResult;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\SavePaymentMethodResult;
use Payum\Core\Result\StoredPaymentMethodDetails;
use Payum\Core\Result\StoredPaymentMethodStatus;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\TokenPurpose;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

final class StoredPaymentMethodCommandsTest extends TestCase
{
    private InMemoryReferenceIndex $index;

    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];

        $this->index = new InMemoryReferenceIndex();
    }

    public function testShouldDeriveTheCapabilities(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');

        $this->assertInstanceOf(Gateway::class, $gateway);
        $this->assertContains(Capability::SavePaymentMethod, $gateway->capabilities());
        $this->assertContains(Capability::DeletePaymentMethod, $gateway->capabilities());
        $this->assertSame(Capability::SavePaymentMethod, SavePaymentMethodCommand::capability());
        $this->assertSame(Capability::DeletePaymentMethod, DeletePaymentMethodCommand::capability());
    }

    public function testShouldCarryTheMethodAsTheSubject(): void
    {
        $method = new StoredPaymentMethod();
        $token = $this->createMock(TokenInterface::class);

        $this->assertSame($method, SavePaymentMethodCommand::forMethod($method, 'key_1')->subject());
        $this->assertSame('key_1', SavePaymentMethodCommand::forMethod($method, 'key_1')->idempotencyKey);
        $this->assertSame($token, SavePaymentMethodCommand::forToken($token)->token());
        $this->assertNull(SavePaymentMethodCommand::forToken($token)->subject());
        $this->assertSame($method, DeletePaymentMethodCommand::forMethod($method)->method());
    }

    public function testShouldRefuseASaveWithNothingToSaveInto(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('needs either a token or a stored payment method');

        new SavePaymentMethodCommand();
    }

    public function testSavedShouldActivateTheMethod(): void
    {
        $payum = $this->buildPayum();
        $method = $this->savedMethod($payum);
        AcmeSaveHandler::$setupDone = true;

        $result = $payum->getGateway('acme')->execute(SavePaymentMethodCommand::forMethod($method));

        $this->assertInstanceOf(SavePaymentMethodResult::class, $result);
        $this->assertNull($result->status);
        $this->assertSame('seti_1', $result->transactionId);
        $this->assertSame(StoredPaymentMethodStatus::Active, $method->getStatus());
        $this->assertSame('pm_1', $method->getReference());
        $this->assertSame('4242', $method->getLast4());
    }

    public function testPendingShouldRedirect(): void
    {
        $payum = $this->buildPayum();
        $method = $this->savedMethod($payum);
        $token = $payum->getTokenIssuer()->create(TokenPurpose::SavePaymentMethod, 'acme', $method, 'saved.php');

        $response = $payum->savePaymentMethod(Request::create($token->getTargetUrl()));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('https://acme.test/setup', $response->getTargetUrl());
        $this->assertStringContainsString('save-payment-method.php', $token->getTargetUrl());
        $this->assertStringContainsString('saved.php', $token->getAfterUrl());
    }

    public function testShouldGoToTheAfterUrlOnceSaved(): void
    {
        $payum = $this->buildPayum();
        $method = $this->savedMethod($payum);
        $token = $payum->getTokenIssuer()->create(TokenPurpose::SavePaymentMethod, 'acme', $method, 'saved.php');
        AcmeSaveHandler::$setupDone = true;

        $response = $payum->savePaymentMethod(Request::create($token->getTargetUrl()));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($token->getAfterUrl(), $response->getTargetUrl());
    }

    public function testDeletedShouldRevokeTheMethod(): void
    {
        $payum = $this->buildPayum();
        $method = $this->savedMethod($payum);
        $method->apply(StoredPaymentMethodDetails::active('pm_1'));

        $result = $payum->getGateway('acme')->execute(DeletePaymentMethodCommand::forMethod($method));

        $this->assertInstanceOf(DeletePaymentMethodResult::class, $result);
        $this->assertNull($result->status);
        $this->assertSame(StoredPaymentMethodStatus::Revoked, $method->getStatus());
        $this->assertSame('pm_1', $method->getReference());
    }

    public function testFailedShouldLeaveTheMethodAlone(): void
    {
        $failure = new Failure(FailureReason::Declined);

        $this->assertSame($failure, SavePaymentMethodResult::failed($failure)->failure);
        $this->assertNull(SavePaymentMethodResult::failed($failure)->storedPaymentMethod());
        $this->assertTrue(SavePaymentMethodResult::accepted('seti_1')->isAccepted());
        $this->assertNull(SavePaymentMethodResult::accepted('seti_1')->storedPaymentMethod());
        $this->assertSame($failure, DeletePaymentMethodResult::failed($failure)->failure);
        $this->assertNull(DeletePaymentMethodResult::failed($failure)->storedPaymentMethod());
    }

    public function testANotifyAboutAMethodResolvesToIt(): void
    {
        $payum = $this->buildPayum();
        $method = $this->savedMethod($payum);
        AcmeSaveHandler::$setupDone = true;
        $payum->getGateway('acme')->execute(SavePaymentMethodCommand::forMethod($method));
        AcmeMethodNotifyHandler::$reference = 'pm_1';

        $payum->getGateway('acme')->execute(NotifyCommand::forGateway());

        $this->assertInstanceOf(StoredPaymentMethodInterface::class, AcmeMethodNotifyHandler::$seen);
        $this->assertSame(StoredPaymentMethodStatus::Revoked, AcmeMethodNotifyHandler::$seen->getStatus());

        $identity = $payum->getStorage(StoredPaymentMethod::class)->identify($method);
        $reloaded = $payum->getStorage(StoredPaymentMethod::class)->find($identity->getId());
        $this->assertInstanceOf(StoredPaymentMethod::class, $reloaded);
        $this->assertSame(StoredPaymentMethodStatus::Revoked, $reloaded->getStatus());
    }

    /**
     * @param Payum<object> $payum
     */
    private function savedMethod(Payum $payum): StoredPaymentMethod
    {
        $method = new StoredPaymentMethod();
        $payum->getStorage(StoredPaymentMethod::class)->update($method);

        return $method;
    }

    /**
     * @return Payum<object>
     */
    private function buildPayum(): Payum
    {
        AcmeSaveHandler::$setupDone = false;
        AcmeMethodNotifyHandler::$reference = null;
        AcmeMethodNotifyHandler::$seen = null;

        return (new PayumBuilder())
            ->addDefaultStorages()
            ->setReferenceIndex($this->index)
            ->addGlobalService(ServerRequestInterface::class, Psr17FactoryDiscovery::findServerRequestFactory()
                ->createServerRequest('POST', 'https://payum.dev/webhooks/acme'))
            ->registerGateway('acme', new AcmeStoredMethodsConfig())
            ->getPayum();
    }
}

final class AcmeStoredMethodsConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return AcmeStoredMethodsGateway::class;
    }
}

final class AcmeStoredMethodsGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return AcmeStoredMethodsConfig::class;
    }

    public function handlers(): array
    {
        return [AcmeSaveHandler::class, AcmeDeleteHandler::class, AcmeMethodNotifyHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://acme.test/logo.svg');
    }

    public function name(): string
    {
        return 'Acme Stored Methods';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://acme.test');
    }
}

final class AcmeSaveHandler implements SavePaymentMethodHandlerInterface
{
    public static bool $setupDone = false;

    public function handle(SavePaymentMethodCommand $command, Context $context): SavePaymentMethodResult
    {
        if (! self::$setupDone) {
            return SavePaymentMethodResult::pending(new Redirect('https://acme.test/setup'));
        }

        return SavePaymentMethodResult::saved(StoredPaymentMethodDetails::active('pm_1', last4: '4242'), 'seti_1');
    }
}

final class AcmeDeleteHandler implements DeletePaymentMethodHandlerInterface
{
    public function handle(DeletePaymentMethodCommand $command, Context $context): DeletePaymentMethodResult
    {
        return DeletePaymentMethodResult::deleted();
    }
}

final class AcmeMethodNotifyHandler implements NotifyHandlerInterface
{
    public static ?string $reference = null;

    public static ?StoredPaymentMethodInterface $seen = null;

    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        self::$seen = $context->storedPaymentMethod();

        return null === self::$seen
            ? NotifyResult::ignored()
            : NotifyResult::handled()->withStoredPaymentMethod(StoredPaymentMethodDetails::revoked());
    }

    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        return WebhookEvent::verified([], 'evt_1', 'payment_method.detached', null, self::$reference);
    }
}
