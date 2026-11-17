<?php

namespace Payum\Core\Registry;

use Closure;
use Payum\Core\Config\GatewayConfig as TypedGatewayConfig;
use Payum\Core\Exception\GatewayUnavailableException;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Storage\StorageInterface;
use Throwable;

/**
 * @template TModel of object
 * @implements RegistryInterface<TModel>
 */
class DynamicRegistry implements RegistryInterface
{
    /**
     * @var GatewayInterface[]
     */
    private array $gateways = [];

    /**
     * @var StorageInterface<GatewayConfigInterface>
     */
    private StorageInterface $gatewayConfigStore;

    /**
     * @var GatewayFactoryRegistryInterface|RegistryInterface<TModel>
     */
    private GatewayFactoryRegistryInterface | RegistryInterface $gatewayFactoryRegistry;

    /**
     * @deprecated since 2.0.0, will be removed in 3.0. Wrap this registry in a
     *             Payum\Core\Registry\FallbackRegistry to reach another registry's gateway factories.
     */
    private bool $backwardCompatibility = true;

    /**
     * @param StorageInterface<GatewayConfigInterface> $gatewayConfigStore
     * @param array<string, class-string<PaymentGateway>> $gatewayTypes the gateway class a stored config's 'type' option may name
     * @param (Closure(string, TypedGatewayConfig): GatewayInterface)|null $buildGateway builds a gateway the way PayumBuilder::registerGateway() does
     */
    public function __construct(
        StorageInterface $gatewayConfigStore,
        GatewayFactoryRegistryInterface $gatewayFactoryRegistry,
        private readonly array $gatewayTypes = [],
        private readonly ?Closure $buildGateway = null,
    ) {
        $this->gatewayConfigStore = $gatewayConfigStore;
        $this->gatewayFactoryRegistry = $gatewayFactoryRegistry;
    }

    public function getGatewayFactory(string $name): GatewayFactoryInterface
    {
        // @deprecated In 3.0 this throws instead of delegating.
        if ($this->backwardCompatibility && $this->gatewayFactoryRegistry instanceof RegistryInterface) {
            return $this->gatewayFactoryRegistry->getGatewayFactory($name);
        }

        throw new InvalidArgumentException(sprintf('Gateway factory "%s" does not exist.', $name));
    }

    public function getGatewayFactories(): array
    {
        // @deprecated In 3.0 this returns an empty array instead of delegating.
        if ($this->backwardCompatibility && $this->gatewayFactoryRegistry instanceof RegistryInterface) {
            return $this->gatewayFactoryRegistry->getGatewayFactories();
        }

        return [];
    }

    public function getGateway(string $name): GatewayInterface
    {
        if (array_key_exists($name, $this->gateways)) {
            return $this->gateways[$name];
        }

        if ($gatewayConfigs = $this->gatewayConfigStore->findBy([
            'gatewayName' => $name,
        ])) {
            $gateway = $this->createGateway(array_shift($gatewayConfigs));
            $this->gateways[$name] = $gateway;

            return $gateway;
        }

        // @deprecated In 3.0 this throws instead of delegating.
        if ($this->backwardCompatibility && $this->gatewayFactoryRegistry instanceof RegistryInterface) {
            return $this->gatewayFactoryRegistry->getGateway($name);
        }

        throw new InvalidArgumentException(sprintf('Gateway "%s" does not exist.', $name));
    }

    /**
     * @return GatewayInterface[]
     */
    public function getGateways(): array
    {
        // @deprecated In 3.0 this returns an empty array instead of delegating.
        if ($this->backwardCompatibility && $this->gatewayFactoryRegistry instanceof RegistryInterface) {
            return $this->gatewayFactoryRegistry->getGateways();
        }

        $gateways = [];
        foreach ($this->gatewayConfigStore->findBy([]) as $gatewayConfig) {
            /** @var GatewayConfigInterface $gatewayConfig */
            $gateways[$gatewayConfig->getGatewayName()] = $this->getGateway($gatewayConfig->getGatewayName());
        }

        return $gateways;
    }

    /**
     * @param class-string|object $class
     *
     * @return StorageInterface<object>
     */
    public function getStorage($class): StorageInterface
    {
        // @deprecated In 3.0 this throws instead of delegating.
        if ($this->backwardCompatibility && $this->gatewayFactoryRegistry instanceof RegistryInterface) {
            return $this->gatewayFactoryRegistry->getStorage($class);
        }

        throw new InvalidArgumentException(sprintf(
            'Storage for given class "%s" does not exist.',
            is_object($class) ? $class::class : $class
        ));
    }

    /**
     * @return StorageInterface<object>[]
     */
    public function getStorages(): array
    {
        // @deprecated In 3.0 this returns an empty array instead of delegating.
        if ($this->backwardCompatibility && $this->gatewayFactoryRegistry instanceof RegistryInterface) {
            return $this->gatewayFactoryRegistry->getStorages();
        }

        return [];
    }

    /**
     * @deprecated since 2.0.0, will be removed in 3.0. Wrap this registry in a
     *             Payum\Core\Registry\FallbackRegistry to reach another registry's gateway factories.
     *
     * @param bool $backwardCompatibility
     */
    public function setBackwardCompatibility($backwardCompatibility): void
    {
        $this->backwardCompatibility = $backwardCompatibility;
    }

    /**
     * @return GatewayInterface
     */
    protected function createGateway(GatewayConfigInterface $gatewayConfig)
    {
        $config = $gatewayConfig->getConfig();

        if (! isset($config['factory']) && ! $gatewayConfig->getFactoryName()) {
            return $this->createTypedGateway($gatewayConfig->getGatewayName(), $config);
        }

        if (isset($config['factory'])) {
            $factory = $this->gatewayFactoryRegistry->getGatewayFactory($config['factory']);
            unset($config['factory']);
        } else {
            // BC

            $factory = $this->gatewayFactoryRegistry->getGatewayFactory($gatewayConfig->getFactoryName());
        }

        return $factory->create($config);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function createTypedGateway(string $gatewayName, array $options): GatewayInterface
    {
        $type = $options['type'] ?? null;
        $gatewayClass = is_string($type) ? ($this->gatewayTypes[$type] ?? null) : null;

        if (null === $gatewayClass || null === $this->buildGateway) {
            throw GatewayUnavailableException::unknownType($gatewayName, $type);
        }

        unset($options['type'], $options['encrypted']);

        $configClass = (new $gatewayClass())->configClass();

        try {
            $config = new $configClass(...$options);
        } catch (Throwable $e) {
            throw GatewayUnavailableException::invalidConfig($gatewayName, $configClass, $e);
        }

        return ($this->buildGateway)($gatewayName, $config);
    }
}
