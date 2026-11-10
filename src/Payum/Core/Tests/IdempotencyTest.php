<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use League\Uri\Uri;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Exception\IdempotencyKeyReusedException;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\RefundHandlerInterface;
use Payum\Core\Idempotency\IdempotencyStoreInterface;
use Payum\Core\Idempotency\StorageIdempotencyStore;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Model\IdempotencyRecord;
use Payum\Core\Model\Payment;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\RefundResult;
use Payum\Core\Storage\FilesystemStorage;
use PHPUnit\Framework\TestCase;

final class IdempotencyTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];

        KeyedCaptureHandler::$charges = 0;
        KeyedRefundHandler::$refunds = 0;
    }

    public function testShouldChargeOnceForARepeatedKeyWithNothingConfigured(): void
    {
        $payum = $this->buildPayum();
        $payment = $this->savedPayment($payum);
        $gateway = $payum->getGateway('acme');

        $first = $gateway->execute(CaptureCommand::forPayment($payment, idempotencyKey: 'order-1001-capture'));
        $second = $gateway->execute(CaptureCommand::forPayment($payment, idempotencyKey: 'order-1001-capture'));

        $this->assertSame(1, KeyedCaptureHandler::$charges);
        $this->assertSame('ch_1', $first->transactionId);
        $this->assertSame('ch_1', $second->transactionId);
    }

    public function testShouldReplayAcrossRequestsWithAStoredRecord(): void
    {
        $dir = sys_get_temp_dir() . '/payum-idempotency-' . uniqid('', true);
        mkdir($dir);
        $first = $this->buildPayum(new StorageIdempotencyStore(new FilesystemStorage($dir, IdempotencyRecord::class, 'scope')));
        $payment = $this->savedPayment($first);

        $first->getGateway('acme')->execute(RefundCommand::forPayment($payment, 500, idempotencyKey: 'refund-77'));

        // A second Payum, as in the next HTTP request, reading the same records.
        $second = $this->buildPayum(new StorageIdempotencyStore(new FilesystemStorage($dir, IdempotencyRecord::class, 'scope')));
        $result = $second->getGateway('acme')->execute(RefundCommand::forPayment($payment, 500, idempotencyKey: 'refund-77'));

        $this->assertSame(1, KeyedRefundHandler::$refunds);
        $this->assertInstanceOf(RefundResult::class, $result);
        $this->assertSame('re_1', $result->transactionId);
        $this->assertSame(500, $result->refundedAmount);
    }

    public function testShouldRefuseAKeyReusedForAnotherPayment(): void
    {
        $payum = $this->buildPayum();
        $gateway = $payum->getGateway('acme');

        $gateway->execute(CaptureCommand::forPayment($this->savedPayment($payum), idempotencyKey: 'order-1001-capture'));

        $this->expectException(IdempotencyKeyReusedException::class);

        $gateway->execute(CaptureCommand::forPayment($this->savedPayment($payum), idempotencyKey: 'order-1001-capture'));
    }

    /**
     * @return Payum<object>
     */
    private function buildPayum(?IdempotencyStoreInterface $store = null): Payum
    {
        $builder = (new PayumBuilder())->addDefaultStorages();

        if ($store instanceof IdempotencyStoreInterface) {
            $builder->addGlobalService(IdempotencyStoreInterface::class, $store);
        }

        return $builder
            ->registerGateway('acme', new KeyedAcmeConfig())
            ->getPayum();
    }

    /**
     * @param Payum<object> $payum
     */
    private function savedPayment(Payum $payum): Payment
    {
        $payment = new Payment();
        $payment->setTotalAmount(1000);
        $payment->setCurrencyCode('EUR');
        $payum->getStorage(Payment::class)->update($payment);

        return $payment;
    }
}

final class KeyedAcmeConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return KeyedAcmeGateway::class;
    }
}

final class KeyedAcmeGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return KeyedAcmeConfig::class;
    }

    public function handlers(): array
    {
        return [KeyedCaptureHandler::class, KeyedRefundHandler::class];
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

final class KeyedCaptureHandler implements CaptureHandlerInterface
{
    public static int $charges = 0;

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        ++self::$charges;

        return CaptureResult::captured('ch_' . self::$charges, $context->amount());
    }
}

final class KeyedRefundHandler implements RefundHandlerInterface
{
    public static int $refunds = 0;

    public function handle(RefundCommand $command, Context $context): RefundResult
    {
        ++self::$refunds;

        return RefundResult::refunded('re_' . self::$refunds, $context->amount());
    }
}
