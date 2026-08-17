<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use League\Uri\Uri;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Gateway\DeclaresTemplates;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Model\Payment;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\NextAction\RenderTemplate;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

/**
 * End to end: a gateway that ships only handlers declares its templates, a handler names one, and
 * Payum::capture() renders it.
 */
final class TemplateRenderingTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];
    }

    public function testShouldResolveATemplateTheGatewayShips(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');

        $result = $gateway->execute(CaptureCommand::forPayment($this->buildPayment()));

        $this->assertInstanceOf(RenderTemplate::class, $result->next);
        $this->assertSame('@PayumAcme/obtain_token.html.twig', $result->next->template);
    }

    public function testShouldRenderTheTemplateThroughCapture(): void
    {
        $payum = $this->buildPayum();
        $token = $payum->prepare('acme', $this->buildPayment(), 'done.php');

        $response = $payum->capture([
            'payum_token' => $token,
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('https://acme.test/pay', (string) $response->getContent());
        $this->assertStringContainsString('Pay 123', (string) $response->getContent());
    }

    public function testShouldTranslateRenderTemplateToAnHttpResponseOnTheLegacyReplyPath(): void
    {
        $payum = $this->buildPayum();
        $token = $payum->prepare('acme', $this->buildPayment(), 'done.php');

        $reply = $payum->getGateway('acme')->execute(new Capture($token), true);

        $this->assertInstanceOf(HttpResponse::class, $reply);
        $this->assertStringContainsString('https://acme.test/pay', $reply->getContent());
        $this->assertStringContainsString('Pay 123', $reply->getContent());
    }

    public function testShouldRenderTheNamespacedTemplateTheGatewayShips(): void
    {
        $html = $this->buildPayum()->renderer()->render('@PayumAcme/obtain_token.html.twig', [
            'actionUrl' => 'https://acme.test/pay',
            'amount' => 123,
        ]);

        $this->assertStringContainsString('https://acme.test/pay', $html);
        $this->assertStringContainsString('Pay 123', $html);
    }

    public function testShouldLetAGatewayTemplateIncludeASibling(): void
    {
        $this->assertStringContainsString(
            'partial content',
            $this->buildPayum()->renderer()->render('@PayumAcme/obtain_token.html.twig', [
                'actionUrl' => 'https://acme.test/pay',
                'amount' => 123,
            ]),
        );
    }

    public function testShouldGiveATemplateTheGatewaySubjectAndTokenWithoutTheHandlerPassingThem(): void
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('auto', new AutoContextConfig())
            ->getPayum();

        $token = $payum->prepare('auto', $this->buildPayment(), 'done.php');

        $body = $payum->capture([
            'payum_token' => $token,
        ])->getContent();

        $this->assertStringContainsString('Auto Context', (string) $body);
        $this->assertStringContainsString('https://auto.test/logo.svg', (string) $body);
        $this->assertStringContainsString('123 EUR', (string) $body);
        $this->assertStringContainsString('An order', (string) $body);
        $this->assertStringContainsString($token->getTargetUrl(), (string) $body);
        $this->assertStringContainsString($token->getAfterUrl(), (string) $body);
    }

    private function buildPayment(): Payment
    {
        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123);
        $payment->setDescription('An order');

        return $payment;
    }

    /**
     * @return Payum<StorageRegistryInterface<StorageInterface<TokenInterface>>>
     */
    private function buildPayum(): Payum
    {
        return (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('acme', new AcmeTemplateConfig())
            ->getPayum();
    }
}

final class AcmeTemplateConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return AcmeTemplateGateway::class;
    }
}

final class AcmeTemplateGateway implements PaymentGateway, DeclaresTemplates
{
    public function configClass(): string
    {
        return AcmeTemplateConfig::class;
    }

    public function handlers(): array
    {
        return [AcmeTemplateCaptureHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://acme.test/logo.svg');
    }

    public function name(): string
    {
        return 'Acme Templates';
    }

    public function templateNamespaces(): array
    {
        return [
            'PayumAcme' => __DIR__ . '/Resources/views',
        ];
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://developer.acme.test');
    }
}

final class AcmeTemplateCaptureHandler implements CaptureHandlerInterface
{
    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        return CaptureResult::pending(new RenderTemplate('@PayumAcme/obtain_token.html.twig', [
            'actionUrl' => 'https://acme.test/pay',
            'amount' => 123,
        ]));
    }
}

final class AutoContextConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return AutoContextGateway::class;
    }
}

final class AutoContextGateway implements PaymentGateway, DeclaresTemplates
{
    public function configClass(): string
    {
        return AutoContextConfig::class;
    }

    public function handlers(): array
    {
        return [AutoContextCaptureHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://auto.test/logo.svg');
    }

    public function name(): string
    {
        return 'Auto Context';
    }

    public function templateNamespaces(): array
    {
        return [
            'PayumAutoContext' => __DIR__ . '/Resources/views',
        ];
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://developer.auto.test');
    }
}

final class AutoContextCaptureHandler implements CaptureHandlerInterface
{
    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        return CaptureResult::pending(new RenderTemplate('@PayumAutoContext/auto_context.html.twig'));
    }
}
