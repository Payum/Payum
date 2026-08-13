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
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\NextAction\RenderTemplate;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

/**
 * End to end: a gateway that ships only handlers declares its templates, a handler names one, and
 * Payum::capture() renders it. The gateway deliberately implements no 1.x interfaces, because that is
 * the path on which template paths used never to be registered at all.
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

    public function testShouldRenderATemplateTheGatewayShips(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');

        $html = $gateway->renderer()->render('@PayumAcme/obtain_token.html.twig', [
            'actionUrl' => 'https://acme.test/pay',
            'amount' => 123,
        ]);

        $this->assertStringContainsString('https://acme.test/pay', $html);
        $this->assertStringContainsString('Pay 123', $html);
    }

    public function testShouldStillResolveTheTemplatesCoreShips(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');

        $this->assertStringContainsString(
            '<!DOCTYPE html>',
            $gateway->renderer()->render('@PayumCore/layout.html.twig'),
        );
    }

    public function testShouldRenderTheTemplateThroughCapture(): void
    {
        $payum = $this->buildPayum();
        $token = $payum->prepare('acme', $this->buildPayment(), 'done.php');

        // The default verifier returns a TokenInterface handed to it directly, so no URL round trip.
        $response = $payum->capture([
            'payum_token' => $token,
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('https://acme.test/pay', $response->getContent());
        $this->assertStringContainsString('Pay 123', $response->getContent());
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

    public function templatePaths(): array
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
