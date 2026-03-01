<?php

namespace Payum\PayTheFly;

use InvalidArgumentException;
use Payum\PayTheFly\Exception\InvalidSignatureException;

/**
 * PayTheFly Web3 Crypto Payment API.
 *
 * Handles EIP-712 typed-data signing for on-chain payment/withdrawal requests
 * and HMAC-SHA256 webhook signature verification.
 */
class Api
{
    public const BASE_URL = 'https://pro.paythefly.com';

    /**
     * @var string
     */
    protected $projectId;

    /**
     * @var string
     */
    protected $projectKey;

    /**
     * @var string
     */
    protected $privateKey;

    /**
     * @var int
     */
    protected $chainId;

    /**
     * @var string
     */
    protected $verifyingContract;

    public function __construct(
        string $projectId,
        string $projectKey,
        string $privateKey,
        int $chainId,
        string $verifyingContract
    ) {
        if (empty($projectId)) {
            throw new InvalidArgumentException('The projectId must not be empty.');
        }
        if (empty($projectKey)) {
            throw new InvalidArgumentException('The projectKey must not be empty.');
        }
        if (empty($privateKey)) {
            throw new InvalidArgumentException('The privateKey must not be empty.');
        }
        if ($chainId <= 0) {
            throw new InvalidArgumentException('The chainId must be a positive integer.');
        }
        if (empty($verifyingContract)) {
            throw new InvalidArgumentException('The verifyingContract must not be empty.');
        }

        $this->projectId = $projectId;
        $this->projectKey = $projectKey;
        $this->privateKey = $privateKey;
        $this->chainId = $chainId;
        $this->verifyingContract = $verifyingContract;
    }

    public function getProjectId(): string
    {
        return $this->projectId;
    }

    public function getProjectKey(): string
    {
        return $this->projectKey;
    }

    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    public function getChainId(): int
    {
        return $this->chainId;
    }

    public function getVerifyingContract(): string
    {
        return $this->verifyingContract;
    }

    /**
     * Get the number of decimals for the current chain's native token.
     */
    public function getChainDecimals(): int
    {
        return $this->chainId === Constants::CHAIN_TRON ? 6 : 18;
    }

    /**
     * Get the native token address for the current chain.
     */
    public function getNativeTokenAddress(): string
    {
        return $this->chainId === Constants::CHAIN_TRON
            ? Constants::NATIVE_TOKEN_TRON
            : Constants::NATIVE_TOKEN_BSC;
    }

    /**
     * Convert a human-readable amount (e.g. "0.01") to raw units for EIP-712 signing.
     *
     * @param string $amount Human-readable amount
     * @param int    $decimals Number of decimals (18 for BSC, 6 for TRON)
     *
     * @return string Raw amount as a decimal string (no scientific notation)
     */
    public static function toRawAmount(string $amount, int $decimals): string
    {
        $parts = explode('.', $amount);
        $integer = $parts[0] ?? '0';
        $fraction = $parts[1] ?? '';

        // Pad or truncate the fractional part
        $fraction = str_pad($fraction, $decimals, '0');
        $fraction = substr($fraction, 0, $decimals);

        $raw = ltrim($integer . $fraction, '0');

        return $raw === '' ? '0' : $raw;
    }

    /**
     * Convert raw units back to human-readable amount.
     *
     * @param string $rawAmount Raw units as a decimal string
     * @param int    $decimals  Number of decimals
     *
     * @return string Human-readable amount (e.g. "0.01")
     */
    public static function fromRawAmount(string $rawAmount, int $decimals): string
    {
        $rawAmount = ltrim($rawAmount, '0') ?: '0';

        if (strlen($rawAmount) <= $decimals) {
            $rawAmount = str_pad($rawAmount, $decimals + 1, '0', STR_PAD_LEFT);
        }

        $integer = substr($rawAmount, 0, -$decimals);
        $fraction = substr($rawAmount, -$decimals);

        // Remove trailing zeros from fraction
        $fraction = rtrim($fraction, '0');

        return $fraction === '' ? $integer : $integer . '.' . $fraction;
    }

    /**
     * Build the EIP-712 domain separator hash.
     *
     * @return string 32-byte binary hash
     */
    public function buildDomainSeparator(): string
    {
        $typeHash = Keccak::hash(
            'EIP712Domain(string name,string version,uint256 chainId,address verifyingContract)',
            256
        );

        $nameHash = Keccak::hash('PayTheFlyPro', 256);
        $versionHash = Keccak::hash('1', 256);

        $chainIdHex = str_pad(dechex($this->chainId), 64, '0', STR_PAD_LEFT);
        $contractHex = str_pad(
            strtolower(ltrim($this->verifyingContract, '0x')),
            64,
            '0',
            STR_PAD_LEFT
        );

        $encoded = hex2bin($typeHash)
            . hex2bin($nameHash)
            . hex2bin($versionHash)
            . hex2bin($chainIdHex)
            . hex2bin($contractHex);

        return Keccak::hash($encoded, 256, true);
    }

    /**
     * Build the EIP-712 struct hash for a PaymentRequest.
     *
     * @param string $token    Token contract address
     * @param string $amount   Raw amount (uint256 as decimal string)
     * @param string $serialNo Order serial number
     * @param int    $deadline Unix timestamp deadline
     *
     * @return string 32-byte binary hash
     */
    public function buildPaymentStructHash(
        string $token,
        string $amount,
        string $serialNo,
        int $deadline
    ): string {
        $typeHash = Keccak::hash(
            'PaymentRequest(string projectId,address token,uint256 amount,string serialNo,uint256 deadline)',
            256
        );

        $projectIdHash = Keccak::hash($this->projectId, 256);
        $serialNoHash = Keccak::hash($serialNo, 256);

        $tokenHex = str_pad(strtolower(ltrim($token, '0x')), 64, '0', STR_PAD_LEFT);
        $amountHex = str_pad(self::decToHex($amount), 64, '0', STR_PAD_LEFT);
        $deadlineHex = str_pad(dechex($deadline), 64, '0', STR_PAD_LEFT);

        $encoded = hex2bin($typeHash)
            . hex2bin($projectIdHash)
            . hex2bin($tokenHex)
            . hex2bin($amountHex)
            . hex2bin($serialNoHash)
            . hex2bin($deadlineHex);

        return Keccak::hash($encoded, 256, true);
    }

    /**
     * Build the EIP-712 struct hash for a WithdrawalRequest.
     *
     * @param string $user     User wallet address
     * @param string $token    Token contract address
     * @param string $amount   Raw amount (uint256 as decimal string)
     * @param string $serialNo Order serial number
     * @param int    $deadline Unix timestamp deadline
     *
     * @return string 32-byte binary hash
     */
    public function buildWithdrawalStructHash(
        string $user,
        string $token,
        string $amount,
        string $serialNo,
        int $deadline
    ): string {
        $typeHash = Keccak::hash(
            'WithdrawalRequest(address user,string projectId,address token,uint256 amount,string serialNo,uint256 deadline)',
            256
        );

        $userHex = str_pad(strtolower(ltrim($user, '0x')), 64, '0', STR_PAD_LEFT);
        $projectIdHash = Keccak::hash($this->projectId, 256);
        $tokenHex = str_pad(strtolower(ltrim($token, '0x')), 64, '0', STR_PAD_LEFT);
        $amountHex = str_pad(self::decToHex($amount), 64, '0', STR_PAD_LEFT);
        $serialNoHash = Keccak::hash($serialNo, 256);
        $deadlineHex = str_pad(dechex($deadline), 64, '0', STR_PAD_LEFT);

        $encoded = hex2bin($typeHash)
            . hex2bin($userHex)
            . hex2bin($projectIdHash)
            . hex2bin($tokenHex)
            . hex2bin($amountHex)
            . hex2bin($serialNoHash)
            . hex2bin($deadlineHex);

        return Keccak::hash($encoded, 256, true);
    }

    /**
     * Sign an EIP-712 typed data hash with the project's private key.
     *
     * @param string $structHash 32-byte binary struct hash
     *
     * @return string Hex-encoded signature with 0x prefix
     */
    public function signTypedData(string $structHash): string
    {
        $domainSeparator = $this->buildDomainSeparator();

        // EIP-712: "\x19\x01" + domainSeparator + structHash
        $message = "\x19\x01" . hex2bin($domainSeparator) . hex2bin($structHash);
        $messageHash = Keccak::hash($message, 256, true);

        return EcRecover::sign(hex2bin($messageHash), $this->privateKey);
    }

    /**
     * Build a payment URL for redirecting the user.
     *
     * @param string $amount   Human-readable amount (e.g. "0.01")
     * @param string $serialNo Order serial number
     * @param int    $deadline Unix timestamp deadline
     * @param string $token    Token contract address (defaults to native token)
     *
     * @return string Full payment URL
     */
    public function buildPaymentUrl(
        string $amount,
        string $serialNo,
        int $deadline,
        string $token = null
    ): string {
        if ($token === null) {
            $token = $this->getNativeTokenAddress();
        }

        $rawAmount = self::toRawAmount($amount, $this->getChainDecimals());
        $structHash = $this->buildPaymentStructHash($token, $rawAmount, $serialNo, $deadline);
        $signature = $this->signTypedData($structHash);

        $params = [
            'chainId' => $this->chainId,
            'projectId' => $this->projectId,
            'amount' => $amount,
            'serialNo' => $serialNo,
            'deadline' => $deadline,
            'signature' => $signature,
            'token' => $token,
        ];

        return self::BASE_URL . '/pay?' . http_build_query($params);
    }

    /**
     * Build a withdrawal URL for redirecting the user.
     *
     * @param string $user     User wallet address
     * @param string $amount   Human-readable amount (e.g. "0.01")
     * @param string $serialNo Order serial number
     * @param int    $deadline Unix timestamp deadline
     * @param string $token    Token contract address (defaults to native token)
     *
     * @return string Full withdrawal URL
     */
    public function buildWithdrawalUrl(
        string $user,
        string $amount,
        string $serialNo,
        int $deadline,
        string $token = null
    ): string {
        if ($token === null) {
            $token = $this->getNativeTokenAddress();
        }

        $rawAmount = self::toRawAmount($amount, $this->getChainDecimals());
        $structHash = $this->buildWithdrawalStructHash($user, $token, $rawAmount, $serialNo, $deadline);
        $signature = $this->signTypedData($structHash);

        $params = [
            'chainId' => $this->chainId,
            'projectId' => $this->projectId,
            'amount' => $amount,
            'serialNo' => $serialNo,
            'deadline' => $deadline,
            'signature' => $signature,
            'token' => $token,
            'user' => $user,
        ];

        return self::BASE_URL . '/withdraw?' . http_build_query($params);
    }

    /**
     * Verify a webhook signature.
     *
     * @param string $data      JSON data string from the webhook body
     * @param string $sign      HMAC-SHA256 hex signature
     * @param int    $timestamp Unix timestamp from the webhook body
     *
     * @return bool True if the signature is valid
     */
    public function verifyWebhookSignature(string $data, string $sign, int $timestamp): bool
    {
        $payload = $data . '.' . $timestamp;
        $expectedSign = hash_hmac('sha256', $payload, $this->projectKey);

        return hash_equals($expectedSign, $sign);
    }

    /**
     * Verify and parse a webhook request body.
     *
     * @param string $rawBody Raw POST body (JSON)
     *
     * @return array Decoded webhook data
     *
     * @throws InvalidSignatureException If the signature is invalid
     */
    public function parseWebhook(string $rawBody): array
    {
        $body = json_decode($rawBody, true);

        if (! is_array($body) || ! isset($body['data'], $body['sign'], $body['timestamp'])) {
            throw new InvalidSignatureException('Invalid webhook body structure.');
        }

        if (! $this->verifyWebhookSignature($body['data'], $body['sign'], (int) $body['timestamp'])) {
            throw new InvalidSignatureException('Webhook signature verification failed.');
        }

        $data = json_decode($body['data'], true);

        if (! is_array($data)) {
            throw new InvalidSignatureException('Invalid webhook data JSON.');
        }

        return $data;
    }

    /**
     * Convert a decimal string to hex (for uint256 encoding).
     */
    public static function decToHex(string $dec): string
    {
        if ($dec === '0') {
            return '0';
        }

        $hex = '';
        $dec = ltrim($dec, '0') ?: '0';

        while (bccomp($dec, '0') > 0) {
            $remainder = bcmod($dec, '16');
            $hex = dechex((int) $remainder) . $hex;
            $dec = bcdiv($dec, '16', 0);
        }

        return $hex ?: '0';
    }
}
