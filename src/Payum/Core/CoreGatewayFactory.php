<?php

namespace Payum\Core;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use LogicException;
use Payum\Core\Action\AuthorizePaymentAction;
use Payum\Core\Action\CapturePaymentAction;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetCurrencyAction;
use Payum\Core\Action\GetTokenAction;
use Payum\Core\Action\PayoutPayoutAction;
use Payum\Core\Bridge\PlainPhp\Action\GetHttpRequestAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Twig\Action\RenderTemplateAction;
use Payum\Core\Bridge\Twig\TwigUtil;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Twig\Environment;
use Twig\Loader\ChainLoader;

class CoreGatewayFactory implements GatewayFactoryInterface
{
    /**
     * @var array<string, mixed>
     */
    protected array $defaultConfig = [];

    /**
     * @param array<string, mixed> $defaultConfig
     */
    public function __construct(array $defaultConfig = [])
    {
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function create(array $config = []): Gateway
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->createConfig());

        $gateway = new Gateway();

        $this->buildClosures($config);

        $this->buildActions($gateway, $config);
        $this->buildApis($gateway, $config);
        $this->buildExtensions($gateway, $config);

        return $gateway;
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    public function createConfig(array $config = []): array
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->defaultConfig);

        $config->defaults([
            'httplug.message_factory' => fn (ArrayObject $config) => Psr17FactoryDiscovery::findRequestFactory(),
            'httplug.stream_factory' => fn (ArrayObject $config) => Psr17FactoryDiscovery::findStreamFactory(),
            'httplug.client' => static fn (ArrayObject $config) => Psr18ClientDiscovery::find(),
            'payum.http_client' => fn (ArrayObject $config) => $config['httplug.client'],
            'payum.template.layout' => '@PayumCore/layout.html.twig',

            'twig.env' => fn () => new Environment(new ChainLoader()),
            'twig.register_paths' => function (ArrayObject $config) {
                $twig = $config['twig.env'];
                if (! $twig instanceof Environment) {
                    throw new LogicException(sprintf(
                        'The `twig.env config option must contains instance of Twig\Environment but got %s`',
                        get_debug_type($twig)
                    ));
                }

                TwigUtil::registerPaths($twig, $config['payum.paths']);

                return null;
            },
            'payum.action.get_http_request' => new GetHttpRequestAction(),
            'payum.action.capture_payment' => new CapturePaymentAction(),
            'payum.action.authorize_payment' => new AuthorizePaymentAction(),
            'payum.action.payout_payout' => new PayoutPayoutAction(),
            'payum.action.execute_same_request_with_model_details' => new ExecuteSameRequestWithModelDetailsAction(),
            'payum.action.render_template' => fn (ArrayObject $config) => new RenderTemplateAction($config['twig.env'], $config['payum.template.layout']),
            'payum.extension.endless_cycle_detector' => new EndlessCycleDetectorExtension(),
            'payum.action.get_currency' => fn (ArrayObject $config) => new GetCurrencyAction(),
            'payum.prepend_actions' => [],
            'payum.prepend_extensions' => [],
            'payum.prepend_apis' => [],
            'payum.default_options' => [],
            'payum.required_options' => [],

            'payum.api.http_client' => fn (ArrayObject $config) => $config['payum.http_client'],

            'payum.security.token_storage' => null,
        ]);

        if ($config['payum.security.token_storage']) {
            $config['payum.action.get_token'] = static fn (ArrayObject $config) => new GetTokenAction($config['payum.security.token_storage']);
        }

        $config['payum.paths'] = array_replace([
            'PayumCore' => __DIR__ . '/Resources/views',
        ], $config['payum.paths'] ?: []);

        return (array) $config;
    }

    protected function buildClosures(ArrayObject $config): void
    {
        // with higher priority
        foreach (['httplug.message_factory', 'httplug.stream_factory', 'httplug.client', 'payum.http_client', 'payum.paths', 'twig.env', 'twig.register_paths'] as $name) {
            $value = $config[$name];
            if (is_callable($value)) {
                $config[$name] = $value($config);
            }
        }

        foreach ($config as $name => $value) {
            if (is_callable($value) && ! (is_string($value) && function_exists('\\' . $value))) {
                $config[$name] = $value($config);
            }
        }
    }

    protected function buildActions(Gateway $gateway, ArrayObject $config): void
    {
        foreach ($config as $name => $value) {
            if (str_starts_with($name, 'payum.action')) {
                $prepend = in_array($name, $config['payum.prepend_actions'], true);

                $gateway->addAction($value, $prepend);
            }
        }
    }

    protected function buildApis(Gateway $gateway, ArrayObject $config): void
    {
        foreach ($config as $name => $value) {
            if (str_starts_with($name, 'payum.api')) {
                $prepend = in_array($name, $config['payum.prepend_apis'], true);

                $gateway->addApi($value, $prepend);
            }
        }
    }

    protected function buildExtensions(Gateway $gateway, ArrayObject $config): void
    {
        foreach ($config as $name => $value) {
            if (str_starts_with($name, 'payum.extension')) {
                $prepend = in_array($name, $config['payum.prepend_extensions'], true);

                $gateway->addExtension($value, $prepend);
            }
        }
    }
}
