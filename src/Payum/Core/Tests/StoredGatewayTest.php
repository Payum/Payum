<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use InvalidArgumentException;
use League\Uri\Uri;
use Payum\Core\Command\SyncCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Exception\GatewayUnavailableException;
use Payum\Core\Exception\InvalidArgumentException as PayumInvalidArgumentException;
use Payum\Core\Gateway;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\SyncHandlerInterface;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Model\GatewayConfig as StoredGatewayConfig;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\SyncResult;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;

final class StoredGatewayTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];
    }

    public function testShouldBuildAStoredConfigLikeARegisteredOne(): void
    {
        $payum = $this->buildPayum([
            'acme_eu' => [
                'type' => 'acme',
                'accountId' => 'acct_eu',
            ],
        ]);

        $gateway = $payum->getGateway('acme_eu');
        $result = $gateway->execute(SyncCommand::forPayment(new Payment()));

        $this->assertInstanceOf(Gateway::class, $gateway);
        $this->assertSame('acme_eu', $gateway->getName());
        $this->assertInstanceOf(SyncResult::class, $result);
        $this->assertSame('acct_eu', $result->transactionId);
    }

    public function testShouldBuildAStoredGatewayOnlyWhenAskedForAndOnlyOnce(): void
    {
        $finds = 0;
        $payum = $this->buildPayum([
            'acme_eu' => [
                'type' => 'acme',
                'accountId' => 'acct_eu',
            ],
        ], $finds);

        $this->assertSame(0, $finds);
        $this->assertSame($payum->getGateway('acme_eu'), $payum->getGateway('acme_eu'));
        $this->assertSame(1, $finds);
    }

    public function testShouldIgnoreTheEncryptedFlagACryptoStorageLeavesInTheOptions(): void
    {
        $payum = $this->buildPayum([
            'acme_eu' => [
                'type' => 'acme',
                'accountId' => 'acct_eu',
                'encrypted' => true,
            ],
        ]);

        $this->assertInstanceOf(Gateway::class, $payum->getGateway('acme_eu'));
    }

    public function testShouldKeepBuildingGatewaysRegisteredInCode(): void
    {
        $payum = $this->buildPayum([], $finds, static fn (PayumBuilder $builder) => $builder->registerGateway('acme_us', new StoredAcmeConfig('acct_us')));

        $result = $payum->getGateway('acme_us')->execute(SyncCommand::forPayment(new Payment()));

        $this->assertSame('acct_us', $result->transactionId);
    }

    public function testShouldRefuseATypeThatIsNotRegistered(): void
    {
        $payum = $this->buildPayum([
            'gone' => [
                'type' => 'uninstalled',
            ],
        ]);

        $this->expectException(GatewayUnavailableException::class);
        $this->expectExceptionMessage('Gateway "gone" is stored with type "uninstalled", which is not registered.');

        $payum->getGateway('gone');
    }

    public function testShouldRefuseAStoredConfigWithNoType(): void
    {
        $payum = $this->buildPayum([
            'untyped' => [
                'accountId' => 'acct_eu',
            ],
        ]);

        $this->expectException(GatewayUnavailableException::class);
        $this->expectExceptionMessage('Gateway "untyped" is stored with type none');

        $payum->getGateway('untyped');
    }

    public function testShouldRefuseOptionsTheConfigCannotBeBuiltFrom(): void
    {
        $payum = $this->buildPayum([
            'broken' => [
                'type' => 'acme',
                'accountId' => '',
            ],
        ]);

        $e = null;
        try {
            $payum->getGateway('broken');
        } catch (Throwable $e) {
        }

        $this->assertInstanceOf(GatewayUnavailableException::class, $e);
        $this->assertStringContainsString('Gateway "broken" is stored with options that do not construct ' . StoredAcmeConfig::class, $e->getMessage());
        $this->assertInstanceOf(InvalidArgumentException::class, $e->getPrevious());
    }

    public function testShouldStillReportAGatewayThatIsNotStoredAsMissing(): void
    {
        $payum = $this->buildPayum([]);

        $this->expectException(PayumInvalidArgumentException::class);
        $this->expectExceptionMessage('Gateway "nope" does not exist.');

        $payum->getGateway('nope');
    }

    public function testShouldRefuseToRegisterATypeThatIsNotAGateway(): void
    {
        $this->expectException(PayumInvalidArgumentException::class);

        (new PayumBuilder())->addGatewayType('acme', stdClass::class);
    }

    /**
     * @param array<string, array<string, mixed>> $rows
     * @param ?callable(PayumBuilder): mixed $configure
     * @return Payum<object>
     */
    private function buildPayum(array $rows, ?int &$finds = 0, ?callable $configure = null): Payum
    {
        $configs = [];
        foreach ($rows as $name => $options) {
            $config = new StoredGatewayConfig();
            $config->setGatewayName($name);
            $config->setConfig($options);
            $configs[$name] = $config;
        }

        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->method('findBy')
            ->willReturnCallback(static function (array $criteria) use ($configs, &$finds): array {
                ++$finds;

                return isset($criteria['gatewayName']) ? array_values(array_filter(
                    $configs,
                    static fn (GatewayConfigInterface $config): bool => $config->getGatewayName() === $criteria['gatewayName'],
                )) : array_values($configs);
            })
        ;

        $builder = (new PayumBuilder())
            ->addDefaultStorages()
            ->setGatewayConfigStorage($storage)
            ->addGatewayType('acme', StoredAcmeGateway::class)
        ;

        if (null !== $configure) {
            $configure($builder);
        }

        return $builder->getPayum();
    }
}

final class StoredAcmeConfig implements GatewayConfig
{
    public function __construct(
        public readonly string $accountId,
    ) {
        if ('' === $accountId) {
            throw new InvalidArgumentException('accountId must not be empty.');
        }
    }

    public function getGatewayClass(): string
    {
        return StoredAcmeGateway::class;
    }
}

final class StoredAcmeGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return StoredAcmeConfig::class;
    }

    public function handlers(): array
    {
        return [StoredAcmeSyncHandler::class];
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

final class StoredAcmeSyncHandler implements SyncHandlerInterface
{
    public function __construct(
        private readonly StoredAcmeConfig $config,
    ) {
    }

    public function handle(SyncCommand $command, Context $context): SyncResult
    {
        return SyncResult::synced(PaymentStatus::Captured, $this->config->accountId);
    }
}
