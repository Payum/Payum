<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use Http\Discovery\Psr17FactoryDiscovery;
use League\Uri\Uri;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Gateway\DeclaresActions;
use Payum\Core\Gateway\DeclaresTemplates;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\GatewayFactory;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetToken;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Template\RendererInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The 1.x information requests, answered from the services 2.0 injects.
 *
 * A gateway written against 1.x dispatches these to core and gets an answer without the application
 * registering an action for it. What is behind them changed - a PSR-7 request, the token storage, the
 * renderer - but the request objects they fill in did not.
 */
final class LegacyInformationRequestsTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];

        LegacyInformationAction::reset();
    }

    public function testShouldAnswerGetHttpRequestFromThePsrRequestTheApplicationRegistered(): void
    {
        $this->capture($this->buildPayum());

        $httpRequest = $this->httpRequest();

        $this->assertSame('POST', $httpRequest->method);
        $this->assertSame('https://payum.dev/notify.php?payum_token=theHash', $httpRequest->uri);
        $this->assertSame([
            'payum_token' => 'theHash',
        ], $httpRequest->query);
        $this->assertSame([
            'payum_token' => 'theHash',
            'status' => 'captured',
        ], $httpRequest->request);
        $this->assertSame('87.65.43.21', $httpRequest->clientIp);
        $this->assertSame('theUserAgent', $httpRequest->userAgent);
        $this->assertSame('{"status":"captured"}', $httpRequest->content);
    }

    public function testShouldAnswerGetTokenFromTheTokenStorage(): void
    {
        $payum = $this->buildPayum();
        $this->seedToken($payum->getTokenStorage());

        $this->capture($payum);

        $token = $this->token()->getToken();

        $this->assertSame('theHash', $token->getHash());
        $this->assertSame('acme', $token->getGatewayName());
    }

    public function testShouldAnswerGetCurrency(): void
    {
        $this->capture($this->buildPayum());

        $currency = $this->currency();

        $this->assertSame('EUR', $currency->alpha3);
        $this->assertSame('978', $currency->numeric);
        $this->assertSame(2, $currency->exp);
    }

    public function testShouldAnswerRenderTemplateThroughTheApplicationsRenderer(): void
    {
        $this->capture($this->buildPayum());

        $rendered = $this->template()->getResult();

        $this->assertStringContainsString('https://acme.test/pay', $rendered);
        $this->assertStringContainsString('Pay 123', $rendered);
    }

    /**
     * Core carries a renderer of its own so that a gateway built straight from the factory can render at
     * all. It must never shadow the application's, or a 1.x action would ignore the layout and the
     * template overrides everything else honours.
     */
    public function testShouldPreferTheApplicationsRendererOverCoresDefault(): void
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(ServerRequestInterface::class, $this->buildServerRequest())
            ->addGlobalService(RendererInterface::class, new LegacyInformationRenderer())
            ->registerGateway('acme', new LegacyInformationConfig())
            ->getPayum();

        $this->capture($payum);

        $this->assertSame('rendered by the application', $this->template()->getResult());
    }

    public function testShouldAnswerTheSameRequestsOnTheOneXFactoryPath(): void
    {
        $tokenStorage = $this->buildPayum()->getTokenStorage();
        $this->seedToken($tokenStorage);

        $gateway = (new LegacyInformationGatewayFactory([], new CoreGatewayFactory([
            'payum.security.token_storage' => $tokenStorage,
            ServerRequestInterface::class => $this->buildServerRequest(),
            'payum.paths' => [
                'PayumAcme' => __DIR__ . '/Resources/views',
            ],
        ])))->create();

        $gateway->execute(new Capture(new ArrayObject()));

        $this->assertSame('POST', $this->httpRequest()->method);
        $this->assertSame('theHash', $this->token()->getToken()->getHash());
        $this->assertSame('EUR', $this->currency()->alpha3);
        $this->assertStringContainsString('Pay 123', $this->template()->getResult());
    }

    /**
     * The one row core cannot fill: obtaining a card needs a form and somewhere to show it, which belongs
     * to the framework integration. Everything else answers without one.
     */
    public function testShouldLeaveObtainCreditCardToTheFrameworkIntegration(): void
    {
        $this->expectException(RequestNotSupportedException::class);

        $this->buildPayum()->getGateway('acme')->execute(new ObtainCreditCard());
    }

    private function httpRequest(): GetHttpRequest
    {
        $this->assertInstanceOf(GetHttpRequest::class, LegacyInformationAction::$http);

        return LegacyInformationAction::$http;
    }

    private function currency(): GetCurrency
    {
        $this->assertInstanceOf(GetCurrency::class, LegacyInformationAction::$currency);

        return LegacyInformationAction::$currency;
    }

    private function template(): RenderTemplate
    {
        $this->assertInstanceOf(RenderTemplate::class, LegacyInformationAction::$template);

        return LegacyInformationAction::$template;
    }

    private function token(): GetToken
    {
        $this->assertInstanceOf(GetToken::class, LegacyInformationAction::$token);

        return LegacyInformationAction::$token;
    }

    /**
     * @param Payum<StorageRegistryInterface<StorageInterface<TokenInterface>>> $payum
     */
    private function capture(Payum $payum): void
    {
        $payum->getGateway('acme')->execute(new Capture(new ArrayObject()));
    }

    /**
     * @return Payum<StorageRegistryInterface<StorageInterface<TokenInterface>>>
     */
    private function buildPayum(): Payum
    {
        return (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(ServerRequestInterface::class, $this->buildServerRequest())
            ->registerGateway('acme', new LegacyInformationConfig())
            ->getPayum();
    }

    /**
     * @param StorageInterface<TokenInterface> $tokenStorage
     */
    private function seedToken(StorageInterface $tokenStorage): void
    {
        $token = $tokenStorage->create();
        $token->setHash('theHash');
        $token->setGatewayName('acme');

        $tokenStorage->update($token);
    }

    private function buildServerRequest(): ServerRequestInterface
    {
        return Psr17FactoryDiscovery::findServerRequestFactory()
            ->createServerRequest('POST', 'https://payum.dev/notify.php?payum_token=theHash', [
                'REMOTE_ADDR' => '87.65.43.21',
            ])
            ->withQueryParams([
                'payum_token' => 'theHash',
            ])
            ->withParsedBody([
                'status' => 'captured',
            ])
            ->withHeader('User-Agent', 'theUserAgent')
            ->withBody(Psr17FactoryDiscovery::findStreamFactory()->createStream('{"status":"captured"}'));
    }
}

final class LegacyInformationConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return LegacyInformationGateway::class;
    }
}

/**
 * A gateway that has not been ported at all: no handlers, only the actions it always had.
 */
final class LegacyInformationGateway implements PaymentGateway, DeclaresActions, DeclaresTemplates
{
    public function actions(): array
    {
        return [LegacyInformationAction::class];
    }

    public function configClass(): string
    {
        return LegacyInformationConfig::class;
    }

    public function handlers(): array
    {
        return [];
    }

    public function logo(): Logo
    {
        return Url::create('https://acme.test/logo.svg');
    }

    public function name(): string
    {
        return 'Acme Legacy';
    }

    public function templateNamespaces(): array
    {
        return [
            'PayumAcme' => __DIR__ . '/Resources/views',
        ];
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://acme.test');
    }
}

/**
 * Nothing here knows about 2.0: it asks core the way a 1.x action always has.
 */
final class LegacyInformationAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public static ?GetCurrency $currency = null;

    public static ?GetHttpRequest $http = null;

    public static ?RenderTemplate $template = null;

    public static ?GetToken $token = null;

    public static function reset(): void
    {
        self::$http = null;
        self::$currency = null;
        self::$template = null;
        self::$token = null;
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->gateway->execute($httpRequest = new GetHttpRequest());
        $this->gateway->execute($currency = new GetCurrency('EUR'));
        $this->gateway->execute($template = new RenderTemplate('@PayumAcme/obtain_token.html.twig', [
            'actionUrl' => 'https://acme.test/pay',
            'amount' => 123,
        ]));
        $this->gateway->execute($token = new GetToken($httpRequest->query['payum_token']));

        self::$http = $httpRequest;
        self::$currency = $currency;
        self::$template = $template;
        self::$token = $token;
    }

    public function supports($request): bool
    {
        return $request instanceof Capture;
    }
}

final class LegacyInformationRenderer implements RendererInterface
{
    public function render(string $template, array $context = []): string
    {
        return 'rendered by the application';
    }
}

final class LegacyInformationGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'acme_legacy',
            'payum.factory_title' => 'Acme Legacy',
            'payum.action.capture' => new LegacyInformationAction(),
        ]);
    }
}
