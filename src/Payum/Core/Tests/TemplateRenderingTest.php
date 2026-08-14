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
use Payum\Core\Template\RendererInterface;
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
        $this->assertSame('payum.template.acme.obtain_token', $result->next->template);
    }

    public function testShouldRenderATemplateTheGatewayShips(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');

        $html = $gateway->renderer()->render('payum.template.acme.obtain_token', [
            'actionUrl' => 'https://acme.test/pay',
            'amount' => 123,
        ]);

        $this->assertStringContainsString('https://acme.test/pay', $html);
        $this->assertStringContainsString('Pay 123', $html);
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

    public function testShouldLetTheApplicationOverrideTheTemplateWithAnotherEngine(): void
    {
        $custom = $this->createMock(RendererInterface::class);
        $custom
            ->expects($this->once())
            ->method('render')
            ->with('/app/views/obtain_token.custom', $this->anything())
            ->willReturn('rendered by the application');

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('acme', new AcmeTemplateConfig())
            ->setTemplate('payum.template.acme.obtain_token', '/app/views/obtain_token.custom')
            ->addRenderer('custom', $custom)
            ->getPayum();

        $token = $payum->prepare('acme', $this->buildPayment(), 'done.php');

        $this->assertSame('rendered by the application', $payum->capture([
            'payum_token' => $token,
        ])->getContent());
    }

    public function testShouldLetAGatewayTemplateIncludeASibling(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');

        $this->assertStringContainsString(
            'partial content',
            $gateway->renderer()->render('@PayumAcme/obtain_token.html.twig', [
                'actionUrl' => 'https://acme.test/pay',
                'amount' => 123,
            ]),
        );
    }

    public function testShouldLetTheApplicationOverrideANamespacedTemplate(): void
    {
        $custom = $this->createMock(RendererInterface::class);
        $custom
            ->expects($this->once())
            ->method('render')
            ->with('/app/views/obtain_token.custom', $this->anything())
            ->willReturn('rendered by the application');

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('acme', new AcmeTemplateConfig())
            ->setTemplate('@PayumAcme/obtain_token.html.twig', '/app/views/obtain_token.custom')
            ->addRenderer('custom', $custom)
            ->getPayum();

        $this->assertSame(
            'rendered by the application',
            $payum->getGateway('acme')->renderer()->render('@PayumAcme/obtain_token.html.twig'),
        );
    }

    private function buildPayment(): Payment
    {
        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123);

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

    public function templates(): array
    {
        return [
            'PayumAcme' => __DIR__ . '/Resources/views',
            'payum.template.acme.obtain_token' => __DIR__ . '/Resources/views/obtain_token.html.twig',
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
        return CaptureResult::pending(new RenderTemplate('payum.template.acme.obtain_token', [
            'actionUrl' => 'https://acme.test/pay',
            'amount' => 123,
        ]));
    }
}
