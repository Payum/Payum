<?php

namespace Payum\PayTheFly\Tests;

use InvalidArgumentException;
use Payum\PayTheFly\Api;
use Payum\PayTheFly\Constants;
use Payum\PayTheFly\Exception\InvalidSignatureException;
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    public function testShouldThrowIfProjectIdEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('projectId');

        new Api('', 'key', 'privkey', 56, '0xContract');
    }

    public function testShouldThrowIfProjectKeyEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('projectKey');

        new Api('proj1', '', 'privkey', 56, '0xContract');
    }

    public function testShouldThrowIfPrivateKeyEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('privateKey');

        new Api('proj1', 'key', '', 56, '0xContract');
    }

    public function testShouldThrowIfChainIdInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('chainId');

        new Api('proj1', 'key', 'privkey', 0, '0xContract');
    }

    public function testShouldThrowIfVerifyingContractEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('verifyingContract');

        new Api('proj1', 'key', 'privkey', 56, '');
    }

    public function testShouldReturnCorrectProjectId(): void
    {
        $api = $this->createApi();

        $this->assertSame('test-project-id', $api->getProjectId());
    }

    public function testShouldReturnCorrectChainId(): void
    {
        $api = $this->createApi();

        $this->assertSame(Constants::CHAIN_BSC, $api->getChainId());
    }

    public function testShouldReturnBscDecimalsForBscChain(): void
    {
        $api = $this->createApi(Constants::CHAIN_BSC);

        $this->assertSame(18, $api->getChainDecimals());
    }

    public function testShouldReturnTronDecimalsForTronChain(): void
    {
        $api = $this->createApi(Constants::CHAIN_TRON);

        $this->assertSame(6, $api->getChainDecimals());
    }

    public function testShouldReturnBscNativeTokenForBscChain(): void
    {
        $api = $this->createApi(Constants::CHAIN_BSC);

        $this->assertSame(Constants::NATIVE_TOKEN_BSC, $api->getNativeTokenAddress());
    }

    public function testShouldReturnTronNativeTokenForTronChain(): void
    {
        $api = $this->createApi(Constants::CHAIN_TRON);

        $this->assertSame(Constants::NATIVE_TOKEN_TRON, $api->getNativeTokenAddress());
    }

    public function testToRawAmountBsc18Decimals(): void
    {
        // 0.01 BNB = 10000000000000000 wei
        $this->assertSame('10000000000000000', Api::toRawAmount('0.01', 18));
    }

    public function testToRawAmountBsc18DecimalsWholeNumber(): void
    {
        // 1 BNB = 1000000000000000000 wei
        $this->assertSame('1000000000000000000', Api::toRawAmount('1', 18));
    }

    public function testToRawAmountTron6Decimals(): void
    {
        // 0.01 TRX = 10000 sun
        $this->assertSame('10000', Api::toRawAmount('0.01', 6));
    }

    public function testToRawAmountZero(): void
    {
        $this->assertSame('0', Api::toRawAmount('0', 18));
    }

    public function testToRawAmountLargeAmount(): void
    {
        // 100.5 BNB
        $this->assertSame('100500000000000000000', Api::toRawAmount('100.5', 18));
    }

    public function testFromRawAmountBsc(): void
    {
        $this->assertSame('0.01', Api::fromRawAmount('10000000000000000', 18));
    }

    public function testFromRawAmountTron(): void
    {
        $this->assertSame('0.01', Api::fromRawAmount('10000', 6));
    }

    public function testFromRawAmountWholeNumber(): void
    {
        $this->assertSame('1', Api::fromRawAmount('1000000000000000000', 18));
    }

    public function testDecToHex(): void
    {
        $this->assertSame('0', Api::decToHex('0'));
        $this->assertSame('ff', Api::decToHex('255'));
        $this->assertSame('2386f26fc10000', Api::decToHex('10000000000000000'));
    }

    public function testVerifyWebhookSignatureValid(): void
    {
        $api = $this->createApi();
        $data = '{"serial_no":"ORDER001","tx_hash":"0xabc"}';
        $timestamp = 1700000000;
        $sign = hash_hmac('sha256', $data . '.' . $timestamp, 'test-project-key');

        $this->assertTrue($api->verifyWebhookSignature($data, $sign, $timestamp));
    }

    public function testVerifyWebhookSignatureInvalid(): void
    {
        $api = $this->createApi();
        $data = '{"serial_no":"ORDER001"}';
        $timestamp = 1700000000;

        $this->assertFalse($api->verifyWebhookSignature($data, 'invalidsignature', $timestamp));
    }

    public function testParseWebhookValid(): void
    {
        $api = $this->createApi();
        $innerData = json_encode([
            'serial_no' => 'ORDER001',
            'tx_hash' => '0xabc123',
            'confirmed' => true,
        ]);
        $timestamp = 1700000000;
        $sign = hash_hmac('sha256', $innerData . '.' . $timestamp, 'test-project-key');

        $body = json_encode([
            'data' => $innerData,
            'sign' => $sign,
            'timestamp' => $timestamp,
        ]);

        $result = $api->parseWebhook($body);

        $this->assertSame('ORDER001', $result['serial_no']);
        $this->assertSame('0xabc123', $result['tx_hash']);
        $this->assertTrue($result['confirmed']);
    }

    public function testParseWebhookInvalidBody(): void
    {
        $this->expectException(InvalidSignatureException::class);
        $this->expectExceptionMessage('Invalid webhook body structure');

        $api = $this->createApi();
        $api->parseWebhook('not json');
    }

    public function testParseWebhookInvalidSignature(): void
    {
        $this->expectException(InvalidSignatureException::class);
        $this->expectExceptionMessage('verification failed');

        $api = $this->createApi();
        $body = json_encode([
            'data' => '{"serial_no":"ORDER001"}',
            'sign' => 'badsignature',
            'timestamp' => 1700000000,
        ]);

        $api->parseWebhook($body);
    }

    public function testParseWebhookInvalidDataJson(): void
    {
        $this->expectException(InvalidSignatureException::class);
        $this->expectExceptionMessage('Invalid webhook data JSON');

        $api = $this->createApi();
        $data = 'not-json-data';
        $timestamp = 1700000000;
        $sign = hash_hmac('sha256', $data . '.' . $timestamp, 'test-project-key');

        $body = json_encode([
            'data' => $data,
            'sign' => $sign,
            'timestamp' => $timestamp,
        ]);

        $api->parseWebhook($body);
    }

    public function testBuildPaymentUrlContainsRequiredParams(): void
    {
        $api = $this->createApi();
        $url = $api->buildPaymentUrl('0.01', 'ORDER001', 1700003600);

        $this->assertStringStartsWith('https://pro.paythefly.com/pay?', $url);
        $this->assertStringContainsString('chainId=56', $url);
        $this->assertStringContainsString('projectId=test-project-id', $url);
        $this->assertStringContainsString('amount=0.01', $url);
        $this->assertStringContainsString('serialNo=ORDER001', $url);
        $this->assertStringContainsString('deadline=1700003600', $url);
        $this->assertStringContainsString('signature=0x', $url);
        $this->assertStringContainsString('token=' . urlencode(Constants::NATIVE_TOKEN_BSC), $url);
    }

    public function testBuildWithdrawalUrlContainsRequiredParams(): void
    {
        $api = $this->createApi();
        $url = $api->buildWithdrawalUrl(
            '0x1234567890abcdef1234567890abcdef12345678',
            '0.05',
            'WD001',
            1700003600
        );

        $this->assertStringStartsWith('https://pro.paythefly.com/withdraw?', $url);
        $this->assertStringContainsString('chainId=56', $url);
        $this->assertStringContainsString('projectId=test-project-id', $url);
        $this->assertStringContainsString('amount=0.05', $url);
        $this->assertStringContainsString('serialNo=WD001', $url);
        $this->assertStringContainsString('user=0x1234567890abcdef1234567890abcdef12345678', $url);
        $this->assertStringContainsString('signature=0x', $url);
    }

    public function testBuildPaymentUrlWithCustomToken(): void
    {
        $api = $this->createApi();
        $customToken = '0xdAC17F958D2ee523a2206206994597C13D831ec7';
        $url = $api->buildPaymentUrl('1.5', 'ORDER002', 1700003600, $customToken);

        $this->assertStringContainsString(urlencode($customToken), $url);
    }

    private function createApi(int $chainId = Constants::CHAIN_BSC): Api
    {
        return new Api(
            'test-project-id',
            'test-project-key',
            'a' . str_repeat('0', 63), // Dummy 32-byte hex private key
            $chainId,
            '0x' . str_repeat('1', 40)
        );
    }
}
